<?php

namespace Drupal\form_page\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the form.
 */
class FormPage extends FormBase {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The log drupal.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Class constructor.
   */
  public function __construct(MessengerInterface $messenger, LoggerChannelFactoryInterface $logger_factory) {
    $this->messenger = $messenger;
    $this->logger = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_page';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormstateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('This is a form which will take in a few details and display them.'),
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name:'),
      '#required' => TRUE,
    ];

    $form['birthday'] = [
      '#type' => 'date',
      '#title' => $this->t('Your birth date:'),
      '#required' => TRUE,
    ];

    $form['age'] = [
      '#type' => 'number',
      '#title' => $this->t('Your age:'),
      '#required' => TRUE,
    ];

    $form['gender'] = [
      '#type' => 'select',
      '#title' => $this->t('Your gender:'),
      '#options' => [
        'male' => $this->t('Male'),
        'female' => $this->t('Female'),
        'other' => $this->t('Other'),
        'prefer not to say' => $this->t('Prefer not to say'),
      ],
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $name = $form_state->getValue('name');

    // Checks if string length in the name is valid.
    if (strlen($form_state->getValue('name')) < 2) {
      $form_state->setErrorByName('name', $this->t('Please enter a valid name.'));
    }

    // Checks if name input contains any invalid characters.
    if (!ctype_print($name)) {
      $form_state->setErrorByName('name', $this->t('The name %nameinput contains invalid characters.', [
        '%nameinput' => $name,
      ]));
    }

    $age = $form_state->getValue('age');

    // Checks if age is valid.
    if ($age < 0) {
      $form_state->setErrorByName('age', $this->t('Hmmm...according to various calculations, you aren\'t even born yet!'));
    }

    if ($age > 120) {
      $form_state->setErrorByName('age', $this->t('Invalid age detected.'));
    }

    // Checks if age and birth date correspond.
    $birthday = $form_state->getValue('birthday');

    $ageInput = date("Y");
    if (($ageInput - $birthday) != $age) {
      $form_state->setErrorByName('birthday', $this->t('Your birth date and age do not correspond.'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Displays the information input by the user.
    $this->messenger->addMessage($this->t('You are @name, age @age and were born on @birthday. As gender you selected <em>@gender</em>.', [
      '@name' => $form_state->getValue('name'),
      '@birthday' => $form_state->getValue('birthday'),
      '@gender' => $form_state->getValue('gender'),
      '@age' => $form_state->getValue('age'),
    ]));

    $this->logger->get('form_page')->info('New submission by @user.', [
      '@user' => $form_state->getValue('name'),
    ]);
  }

}
