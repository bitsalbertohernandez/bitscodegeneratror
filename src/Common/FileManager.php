<?php
namespace Drupal\bits_developer_tool\Common;

use Drupal\file\Entity\File;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class FileManager
{

  private $yaml;
  public const PATH_PREFIX = "{modulo}";
  public const ID_CONFIG = "bits_developer_tool.generalconfig";

  public function __construct()
  {
    $this->yaml = new SymfonyYaml();
    $this->config = \Drupal::config(FileManager::ID_CONFIG);
  }

  /**
   * Salvar datos en fichero.
   *
   * @param string $data
   *  Datos para guardar en el fichero.
   * @param string $dir_file
   *  Ruta del archivo.
   * @return boolean
   *  Retorna true si se salvó la información y false en caso contrario.
   */
  public function saveGenerateFile($dir_file, $data)
  {
    // No se ha probado aun...
    if (!file_exists($dir_file)) {
      mkdir($dir_file, 0770, true);
    }
    return $this->saveFile($dir_file, $data);
  }

  /**
   * Copiar configuraciones en archivo YAML.
   *
   * @param string $dir
   *  Ruta del archivo YAML.
   * @param string $type
   *  Tipo de archivo yaml.
   * @param array $data
   *  Configuraciones.
   * @return boolean
   *  Retorna true si se salvó la información y false en caso contrario.
   */
  public function saveYAMLConfig($dir, $type = YAMLType::INFO_FILE, array $data = [])
  {
    $data_file = $this->getYAMLData($dir);
    foreach ($data as $key => $value) {
      if ($type == YAMLType::SERVICES_FILE) {
        $data_file['services'][$key] = $value;
      } else {
        $data_file[$key] = $value;
      }
    }
    $yaml_data = $this->yaml->dump($data_file, 2, 2);
    return $this->saveFile($dir, $yaml_data);
  }

  /**
   * Obtener datos del archivo YAML.
   *
   * @param string $dir
   *  Ruta del archivo.
   * @return array
   *  Configuraciones que contine el fichero.
   */
  public function getYAMLData($dir)
  {
    $content = $this->getFileContent($dir);
    return $this->yaml->parse($content);
  }

  /**
   * Saber si existe una clave en la raiz del archivo YAML.
   *
   * @param string $dir
   *  Ruta del archivo.
   * @param string $key
   *  Clave a buscar.
   * @return boolean
   * Retorna true si existe la clave y false en caso contrario.
   */
  public function existKeyInYAMLFile($dir, $key)
  {
    $yaml_content = $this->getYAMLData($dir);
    $array_key = array_keys($yaml_content);
    return in_array($key, $array_key);
  }

  /**
   * Obtener la ruta a un archivo YAML.
   *
   * @param string $module_name
   *  Nombre del módulo.
   * @param string $type_file
   *  Tipo de archivo contenido en la clase YAMLType.
   * @return string
   *  Ruta del archivo.
   *
   */
  public function getYAMLPath($module_name, $type_file)
  {
    $module_dir = $this->modulePath($module_name);
    return $module_dir . "/$module_name.$type_file";
  }

  /**
   * Obtener la ruta del módulo.
   *
   * @param string $module_name
   *  Nombre del módulo.
   * @return string
   *  Ruta del módulo
   */
  public function modulePath($module_name)
  {
    return drupal_get_path('module', $module_name);
  }

  /**
   * Obtenerla ruta por el tipo de archivo.
   *
   * @param string $type
   *  Tipo de archivo(Controlador, Servicio, Bloque, Formulario).
   * @param string $file_name
   *  Nombre del fichero.
   * @param string $module_name
   *  Nombre del módulo.
   * @return string
   *  Ruta del archivo.
   *
   */
  public function getFilePathByType($module_name, $file_name, $type)
  {
    // ver como accedo a la configuracion de la ruta(Alejandro)
    $dir = $this->modulePath($module_name);
    $config_path = $this->config->get($type);
    $config_path = str_replace(FileManager::PATH_PREFIX, "", $config_path);
    return $dir . $config_path . '/' . $file_name;
  }

  /**
   * Obtener el namespace del tipo de archivo.
   *
   * @param string $module_name
   *  Nombre del módulo.
   * @param string $type
   *  Tipo de archivo de la clase TypeOfFile.
   * @return void
   */
  public function getFileNameSpaceByType($module_name, $type)
  {
    // ver como accedo a la configuracion del namespace(Alejandro)
    $config_namespace = $this->config->get($type);
    return str_replace(FileManager::PATH_PREFIX, $module_name, $config_path);
  }

  /**
   * Obtener el contenido de un archivo
   *
   * @param string $dir
   *  Dirección del archivo.
   * @return string
   *  Contenido del archivo.
   */
  public function getFileContent($dir)
  {
    return file_get_contents($dir);
  }

  /**
   * Guardar datos en fichero.
   *
   * @param string $data
   *  Datos para guardar.
   * @param string $dir_file
   *  Ruta del fichero.
   * @return boolean
   *  Retorna true si se salvó la información y false en caso contrario.
   */
  public function saveFile($dir_file, $data)
  {
    return (boolean)file_put_contents($dir_file, $data);
  }

}
