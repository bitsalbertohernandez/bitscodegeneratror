<?php

namespace Drupal\bits_developer_tool\Services;

use Drupal\bits_developer_tool\Generators\BlockGenerator;
use Drupal\bits_developer_tool\Common\FileManager;
use Drupal\bits_developer_tool\Common\TypeOfFile;
use Drupal\bits_developer_tool\Common\YAMLType;
use Drupal\bits_developer_tool\Common\RegionalUse;

class RegionalBlockBuilder {
  
  private $class;
  
  private $module;
  
  private $identificator;
  
  private $logic_Class;
  
  private $regional_use = 'Drupal\tbo_general\CardBlockBase';
  
  private $regional_extend = "CardBlockBase";
  
  private $regional_property = "configuration_instance";
  
  private $regional_property_comment = '@var \\';
  
  private $configuration_prop = '$configuration';
  
  private $plugin_id_prop = '$plugin_id';
  
  private $plugin_definition_prop = '$plugin_definition';
  
  private $container_interface = '\Symfony\Component\DependencyInjection\ContainerInterface';
  
  private $interface = "Drupal\Core\Plugin\ContainerFactoryPluginInterface";
  
  private $interface_name = 'ContainerFactoryPluginInterface';
  
  /**
   * @var \Drupal\bits_developer_tool\Common\FileManager
   */
  private $file_manager;
  
  /**
   * @var \Drupal\bits_developer_tool\Generators\BlockGenerator
   */
  private $block_generator;
  
  /**
   * @var \Drupal\bits_developer_tool\Common\NameSpacePathConfig
   */
  private $namespace_path;
  
  /**
   * RegionalBlockBuilder constructor.
   */
  public function __construct() {
    $this->file_manager = \Drupal::service('bits_developer.file.manager');
    $this->block_generator = \Drupal::service('bits_developer.block.generator');
    $this->namespace_path = \Drupal::service('bits_developer.namespace.path');
  }
  
  /**
   * Add Class Comments to Blocks
   *
   * @param $class_name Class Name.
   * @param $id_block New block id.
   * @param $admin_label Internacionalization Block label.
   */
  public function addClassComments($class_name, $id_block, $admin_label) {
    $this->block_generator->addClassCommentBlock($class_name, $id_block, $admin_label);
  }
  
  /**
   * Add Implements to a Class
   *
   */
  public function addImplementToClass() {
    $namespace = str_replace(
      FileManager::PATH_PREFIX, $this->module,
      $this->namespace_path->getNameSpace(TypeOfFile::BLOCK)
    );
    
    $this->block_generator->addUse($this->interface);
    $this->block_generator->addImplement($namespace . "\\" . $this->interface_name);
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
    if ($this->generateBlockClass(TypeOfFile::BLOCK)) {
      $this->generateYAMLConfig();
      $this->generateBlockClass(TypeOfFile::BLOCK_LOGIC);
    }
  }
  
  /**
   * Array of Construct Block Comments
   *
   * @return array
   */
  private function constructComments($namespace) {
    $configuration_instance = "$" . $this->regional_property;
    return [
      "Contructor Block Class. \n",
      "@param array $this->configuration_prop \n Block configuration.",
      "@param string $this->plugin_id_prop \n Block identification.",
      "@param mixed $this->plugin_definition_prop \n Plugin definition.",
      "@param $namespace $configuration_instance \n Logic class of block.",
    ];
  }
  
  /**
   * Array of Create Methods Block Comments
   *
   * @return array
   */
  private function createComments() {
    $container = $this->container_interface . ' $container';
    return [
      "Create Block Class. \n",
      "@param $container \n Block container.",
      "@param array $this->configuration_prop \n Block configuration.",
      "@param string $this->plugin_id_prop \n Plugin identification.",
      "@param mixed $this->plugin_definition_prop \n Plugin definition.",
      "\n\n@return static",
    ];
  }
  
  /**
   * Generate Body of Contruct Block Base Class.
   *
   * @return string
   */
  private function generateContructBlockBaseClassBody() {
    $instance = "// Store our dependency. \n" . '$this->' . $this->regional_property . ' = $' . $this->regional_property;
    $parent = "\n\n// Call parent construct method. \n" . 'parent::__construct(' . $this->configuration_prop . ', ' . $this->plugin_id_prop . ', ' . $this->plugin_definition_prop . ');';
    $set_config = "\n\n// Set init config. \n" . '$this->configurationInstance->setConfig($this, $this->configuration);';
    return $instance . $parent . $set_config;
  }
  
  /**
   * Genetate Body of Create Method Block Base Class.
   *
   * @return string
   */
  private function generateCreateBlockBaseClassBody() {
    $ident = "'$this->identificator'";
    $containter = '$container->get(' . $ident . ')';
    return "return new static(\n  $this->configuration_prop,\n  $this->plugin_id_prop,\n  $this->plugin_definition_prop,\n  $containter\n);";
  }
  
  /**
   * Array of Create Arguments
   *
   * @return array
   */
  private function createArguments() {
    
    return [
      ["name" => "container", "type" => $this->container_interface],
      ["name" => "configuration", "type" => "array"],
      ["name" => "plugin_id"],
      ["name" => "plugin_definition"],
    ];
  }
  
  /**
   * Array of Contruct Arguments
   *
   * @return array
   */
  private function constructArguments($config_instance, $config_class) {
    return [
      ["name" => "configuration", "type" => "array"],
      ["name" => "plugin_id"],
      ["name" => "plugin_definition"],
      ["name" => $config_instance, "type" => $config_class],
    ];
  }
  
  /**
   * Create Block Base Class.
   *
   * @param $block_generator
   * @param $namespace_logic
   */
  private function createBlockBase(&$block_generator, $namespace_logic) {
    $namespace = str_replace(FileManager::PATH_PREFIX, $this->module, $this->namespace_path->getNameSpace(TypeOfFile::BLOCK));
    
    $block_generator->addUse($this->regional_use);
    $block_generator->addExtend($namespace . "\\" . $this->regional_extend);
    $block_generator->addNameSpace($namespace);
    $block_generator->addClassProperty($this->regional_property, $this->regional_property_comment . "$namespace_logic\\$this->logic_Class", "", FALSE, 'protected');
    
    // Constructor code.
    $bodyContruct = $this->generateContructBlockBaseClassBody();
    $block_generator->addMethod(
      '__construct',
      $bodyContruct,
      $this->constructComments($namespace_logic . $this->logic_Class),
      $this->constructArguments($this->regional_property, $namespace_logic . $this->logic_Class)
    );
    
    // Create method code.
    $bodyCreate = $this->generateCreateBlockBaseClassBody();
    $create_method = $block_generator->addMethod('create', $bodyCreate, $this->createComments(), $this->createArguments(), 'static');
  }
  
  /**
   * Create Block Class Logic.
   *
   * @param $block_generator
   * @param $namespace_logic
   */
  private function createBlockClassLogic(&$block_generator, $namespace_logic) {
    $block_generator = new BlockGenerator();
    $block_generator->addNameSpace($namespace_logic);
  }
  
  /**
   * Generate Path And Code in Base And Logic Class.
   *
   * @param $block_generator
   * @param $class
   *
   * @return array
   */
  private function generatePathAndCode($block_generator, $class) {
    $code = $block_generator->generateClass($class);
    $path = $this->file_manager->modulePath($this->module) . str_replace(FileManager::PATH_PREFIX, '', $this->namespace_path->getPath(TypeOfFile::BLOCK));
    if (!$this->file_manager->pathExist($path)) {
      $this->file_manager->createPath($path);
    }
    $dir_file = $path . '/' . $class . '.php';
    return ['code' => $code, 'dir_file' => $dir_file];
  }
  
  /**
   * Generate Block Class Function.
   *
   * @param $type
   *
   * @return bool
   */
  private function generateBlockClass($type) {
    
    $block_generator = $this->block_generator;
    $success = FALSE;
    $namespace_logic = str_replace(FileManager::PATH_PREFIX, $this->module, $this->namespace_path->getNameSpaceLogic(TypeOfFile::BLOCK));
    $code = "";
    $dir_file = "";
    $dir_module = $this->file_manager->modulePath($this->module, $dir_file);
    if ($type == TypeOfFile::BLOCK) {
      $this->createBlockBase($block_generator, $namespace_logic);
      $path_code = $this->generatePathAndCode($block_generator, $this->class);
      $code = $path_code['code'];
      $dir_file = $path_code['dir_file'];
    }
    else {
      $this->createBlockClassLogic($block_generator, $namespace_logic);
      $path_code = $this->generatePathAndCode($block_generator, $this->logic_Class);
      $code = $path_code['code'];
      $dir_file = $path_code['dir_file'];
    }
    return $this->file_manager->saveFile($dir_file, "<?php \n \n" . $code);
  }
  
  private function generateYAMLConfig() {
    $class = str_replace(FileManager::PATH_PREFIX, $this->module, $this->namespace_path->getNameSpaceLogic(TypeOfFile::BLOCK)) . '\\' . $this->logic_Class;
    $data[$this->identificator]['class'] = $class;
    $data[$this->identificator]['arguments'] = [];
    $yaml_dir = $this->file_manager->getYAMLPath($this->module, YAMLType::SERVICES_FILE);
    return $this->file_manager->saveYAMLConfig($yaml_dir, $data, YAMLType::SERVICES_FILE);
    
  }
}
