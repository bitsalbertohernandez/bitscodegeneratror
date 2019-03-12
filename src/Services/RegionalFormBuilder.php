<?php

namespace Drupal\bits_developer_tool\Services;

use Drupal\bits_developer_tool\Generators\FormGenerator;
use Drupal\bits_developer_tool\Common\FileManager;
use Drupal\bits_developer_tool\Common\TypeOfFile;
use Drupal\bits_developer_tool\Common\YAMLType;
use Drupal\bits_developer_tool\Common\RegionalUse;

class RegionalFormBuilder {
  
  private $class;
  
  private $module;
  
  private $identificator;
  
  private $logic_Class;
  
  private $regional_use = 'use Drupal\Core\Form\FormStateInterface; \n use Drupal\tbo_billing\Form\BillingPaymentSettings;';
  
  private $regional_extend = "FormBase";
  
  private $regional_property = "logic_instance";
  
  private $regional_property_comment = '@var \\';
  
  private $configuration_prop = '$configuration';

  private $form_id;
  
  private $container_interface = '\Symfony\Component\DependencyInjection\ContainerInterface';
  
  private $interface = "Drupal\Core\Plugin\ContainerFactoryPluginInterface";
  
  private $interface_name = 'ContainerFactoryPluginInterface';
  
  /**
   * @var \Drupal\bits_developer_tool\Common\FileManager
   */
  private $file_manager;
  
  /**
   * @var \Drupal\bits_developer_tool\Generators\FormGenerator
   */
  private $form_generator;
  
  /**
   * @var \Drupal\bits_developer_tool\Common\NameSpacePathConfig
   */
  private $namespace_path;
  
  /**
   * RegionalFormBuilder constructor.
   */
  public function __construct() {
    $this->file_manager = \Drupal::service('bits_developer.file.manager');
    $this->form_generator = \Drupal::service('bits_developer.form.generator');
    $this->namespace_path = \Drupal::service('bits_developer.namespace.path');
  }
  
  /**
   * Add Class Comments to Forms
   *
   * @param $class_name Class Name.
   * @param $id_form New form id.
   * @param $admin_label Internacionalization Form label.
   */
  public function addClassComments($class_name, $id_form, $admin_label) {
    $this->form_generator->addClassCommentForm($class_name, $id_form, $admin_label);
  }
  
  /**
   * Add Implements to a Class
   *
   */
  public function addImplementToClass() {
    $namespace = str_replace(
      FileManager::PATH_PREFIX, $this->module,
      $this->namespace_path->getNameSpace(TypeOfFile::FORM)
    );
    
    $this->form_generator->addUse($this->interface);
    $this->form_generator->addImplement($namespace . "\\" . $this->interface_name);
  }
  
  /**
   * Add Class Function.
   *
   * @param $class
   */
  public function addClass($class) {
    $this->class = $class;
  }

  /**
   * Set Form Id.
   *
   * @param $form_id
   */
  public function setFormId($form_id) {
    $this->form_id = $form_id;
  }
  
  /**
   * Add Module Function.
   *
   * @param $module
   */
  public function addModule($module) {
    $this->module = $module;
  }
  
  /**
   * Add Identificator Function.
   *
   * @param $identificator
   */
  public function addIdentificator($identificator) {
    $this->identificator = $identificator;
  }
  
  /**
   * Add Logic Class Function.
   *
   * @param $logic_Class
   */
  public function addLogicClass($logic_Class) {
    $this->logic_Class = $logic_Class;
  }
  
  /**
   * Build Files Function.
   */
  public function buildFiles() {
    if ($this->generateFormClass(TypeOfFile::FORM)) {
      $successYaml = $this->generateYAMLConfig();
      $successClass = $this->generateFormLogicClass(TypeOfFile::FORM_LOGIC);
      return ($successClass && $successYaml);
    }
    return false;
  }
  
  /**
   * Array of Construct Form Comments
   *
   * @return array
   */
  private function constructComments($namespace) {
    $configuration_instance = "$" . $this->regional_property;
    return [
      "Contructor Form Class. \n",
      "@param $namespace $configuration_instance \n Logic class of form.",
    ];
  }
  

  
  /**
   * Generate Body of Contruct Form Base Class.
   *
   * @return string
   */
  private function generateContructFormBaseClassBody() {
    $instance = "// Store our dependency. \n" . '$this->' . $this->regional_property . ' = $' . $this->regional_property.';';
    $set_config = "\n\n\n" . '$this->'.$this->regional_property.'->createInstance($this);';
    return $instance  . $set_config;
  }


  /**
   * Generate body of method getFormId.
   *
   * @return string
   */
  private function generateGetFormIdBody() {

    return '$this->'."$this->regional_property->getFormId();";
  }
  
//  /**
//   * Array of Create Arguments
//   *
//   * @return array
//   */
//  private function createArguments() {
//
//    return [
//      ["name" => "container", "type" => $this->container_interface],
//      ["name" => "configuration", "type" => "array"],
//    ];
//  }
  
  /**
   * Array of Contruct Arguments
   *
   * @return array
   */
  private function constructArguments($config_instance, $config_class) {
    return [
      ["name" => $config_instance, "type" => $config_class],
    ];
  }

  /**
   * Array of Arguments
   *
   * @return array
   */
  private function functionArguments($function) {
    switch ($function) {
      case 'buildForm' :
        return [
          ["name" => "form", "type" => "array"],
          ["name" => "form_state", "type" => "Drupal\Core\Form\FormStateInterface"],
        ]; break;
      case 'validateForm' :
        return [
          ["name" => "form", "type" => "array", "reference" => true],
          ["name" => "form_state", "type" => "Drupal\Core\Form\FormStateInterface"],
        ]; break;
      case 'submitForm' :
        return [
          ["name" => "form", "type" => "array", "reference" => true],
          ["name" => "form_state", "type" => "Drupal\Core\Form\FormStateInterface"],
        ]; break;
    }
  }
  
  /**
   * Create Form Base Class.
   *
   * @param $form_generator
   * @param $namespace_logic
   */
  private function createFormBase(&$form_generator, $namespace_logic) {
    $namespace = str_replace(FileManager::PATH_PREFIX, $this->module, $this->namespace_path->getNameSpace(TypeOfFile::FORM));
    
    $form_generator->addUse('Drupal\Core\Form\FormBase');
    $form_generator->addUse('Drupal\Core\Form\FormStateInterface');
    $form_generator->addExtend($namespace . "\\" . $this->regional_extend);
    $form_generator->addNameSpace($namespace);
    $regional_comment = $this->regional_property_comment.$namespace_logic."\\".$this->logic_Class;
    $form_generator->addClassProperty($this->regional_property, $regional_comment, "", FALSE, 'protected');
    
    // Constructor code.
    $bodyContruct = $this->generateContructFormBaseClassBody();
    $form_generator->addMethod(
      '__construct',
      $bodyContruct,
      $this->constructComments($namespace_logic ."\\". $this->logic_Class),
      $this->constructArguments($this->regional_property, $namespace_logic ."\\". $this->logic_Class)
    );
    $form_generator->addMethod('getFormId',$this->generateGetFormIdBody());
    $form_generator->addMethod('buildForm', "", [], $this->functionArguments('buildForm'));
    $form_generator->addMethod('submitForm', "", [], $this->functionArguments('submitForm'));
    $form_generator->addMethod('validateForm', "", [], $this->functionArguments('validateForm'));
  }
  
  /**
   * Create Form Class Logic.
   *
   * @param $form_generator
   * @param $namespace_logic
   */
  private function createFormClassLogic(&$form_generator, $namespace_logic) {
    $form_generator = new FormGenerator();
    $form_generator->addNameSpace($namespace_logic);
  }
  
  /**
   * Generate Path And Code in Base And Logic Class.
   *
   * @param $form_generator
   * @param $class
   *
   * @return array
   */
  private function generatePathAndCode($form_generator, $class) {
    $code = $form_generator->generateClass($class);
    $path = $this->file_manager->modulePath($this->module) . str_replace(FileManager::PATH_PREFIX, '', $this->namespace_path->getPath(TypeOfFile::FORM));
    if (!$this->file_manager->pathExist($path)) {
      $this->file_manager->createPath($path);
    }
    $dir_file = $path . '/' . $class . '.php';
    return ['code' => $code, 'dir_file' => $dir_file];
  }
  
  /**
   * Generate Form Class Function.
   *
   * @param $type
   *
   * @return bool
   */
  private function  generateFormClass($type) {

    $form_generator = $this->form_generator;
    $success = FALSE;
    $namespace_logic = str_replace(FileManager::PATH_PREFIX, $this->module, $this->namespace_path->getNameSpaceLogic(TypeOfFile::FORM));
    $code = "";
    $dir_file = "";
    $dir_module = $this->file_manager->modulePath($this->module, $dir_file);
    $this->createFormBase($form_generator, $namespace_logic);
    $path_code = $this->generatePathAndCode($form_generator, $this->class);
    $code = $path_code['code'];
    $dir_file = $path_code['dir_file'];
    return $this->file_manager->saveFile($dir_file, "<?php \n \n" . $code);
  }

  /**
   * Generate Form Class Function.
   *
   * @param $type
   *
   * @return bool
   */
  private function  generateFormLogicClass($type) {

    $form_generator = $this->form_generator;
    $success = FALSE;
    $namespace_logic = str_replace(FileManager::PATH_PREFIX, $this->module, $this->namespace_path->getNameSpaceLogic(TypeOfFile::FORM));
    $code = "";
    $dir_file = "";
    $dir_module = $this->file_manager->modulePath($this->module, $dir_file);
    $this->createFormClassLogic($form_generator, $namespace_logic);
    $path_code = $this->generatePathAndCode($form_generator, $this->logic_Class);
    $code = $path_code['code'];
    $dir_file = $path_code['dir_file'];
    return $this->file_manager->saveFile($dir_file, "<?php \n \n" . $code);
  }
  
  private function generateYAMLConfig() {
    $class = str_replace(FileManager::PATH_PREFIX, $this->module, $this->namespace_path->getNameSpaceLogic(TypeOfFile::FORM)) . '\\' . $this->logic_Class;
    $data[$this->identificator]['class'] = $class;
    $data[$this->identificator]['arguments'] = [];
    $yaml_dir = $this->file_manager->getYAMLPath($this->module, YAMLType::SERVICES_FILE);
    return $this->file_manager->saveYAMLConfig($yaml_dir, $data, YAMLType::SERVICES_FILE);
    
  }
}
