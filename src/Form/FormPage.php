<?php

namespace Drupal\form_page\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the form.
 */
class FormPage extends FormBase {

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
    if (!ctype_alpha($name)) {
      $form_state->setErrorByName('name', $this->t('The name %nameinput contains invalid characters.', [
        '%nameinput' => $name,
      ]));
    }

    $age = $form_state->getValue('age');

    // Checks if age is valid.
    if ($age < 0) {
      $form_state->setErrorByName('age', $this->t('Hmmm...according to various calculations, you aren\'t even born yet!'));
    }

    if ($age > 150) {
      $form_state->setErrorByName('age', $this->t('Hmmm...according to various calculations, you aren\'t even born yet!'));
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

    // Displays the values input by the user.
    $messenger = \Drupal::messenger();
    $messenger->addMessage($this->t('Your name is @name and you are @gender. You were born on @birthday and are @age years old.', [
      '@name' => $form_state->getValue('name'),
      '@birthday' => $form_state->getValue('birthday'),
      '@gender' => $form_state->getValue('gender'),
      '@age' => $form_state->getValue('age'),
    ]));
  }

}
