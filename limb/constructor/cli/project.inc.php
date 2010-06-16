<?php

/**
 * @desc Init project meta task
 * @deps project_files,project_shares,project_var_dir,project_init_cms,project_db_load
 * @example project.php init -D DSN=mysqli://root:test@localhost/limb_app?charset=utf8
 */
function task_project_create($argv)
{
}

/**
 * @desc Create new project in specified path
 * @param path
 * @example project.php create_project /var/www/example-limb-project.com
 */
function task_project_files($args)
{
  $proj_dir = taskman_prop('PROJECT_DIR');
  $limb_dir = taskman_prop('LIMB_DIR');

  if(file_exists($proj_dir) && !is_dir($proj_dir))
    throw new Exception('Path is not a dir');

  lmb_require('limb/fs/src/lmbFs.class.php');

  if(!is_dir($proj_dir))
  {
    lmbFs::mkdir($proj_dir . '/www', 0755, true);
    taskman_msg("Created dir $proj_dir/www ...Done\n");
    lmbFs::mkdir($proj_dir . '/lib', 0755, true);
    taskman_msg("Created dir $proj_dir/lib ...Done\n");
  }

  taskman_msg('Limb is copied...');
  lmbFs::cp($limb_dir . '/limb', $proj_dir . '/lib/limb');
  taskman_msg("Done\n");

  taskman_msg('Skel is copied...');
  lmbFs::cp($proj_dir.'/lib/limb/web_app/skel', $proj_dir);
  taskman_msg("Done\n");

  if(lmb_cli_ask_for_accept('Do you need a incubator packages?'))
  {
    $incubator_dir = lmb_cli_ask_for_option('Incubator dir', $limb_dir . '/incubator/limb');
    taskman_msg('Incubator packages is copied...');
    foreach(lmbFs::ls($incubator_dir) as $package_name)
      lmbFs::cp(realpath($incubator_dir), $proj_dir . '/lib/limb');

    taskman_msg("Done\n");
  }
}

/**
 * @desc init cms installation
 * @example project.php init_cms
 */
function task_project_init_cms($args)
{
  $application = <<<EOD
<?php
lmb_require('limb/cms/src/lmbCmsApplication.class.php');

class LimbApplication extends lmbCmsApplication
{
  /*function __construct()
  {
    //register your own custom filter chain here
  }
}

EOD;
  $setup = <<<EOD
<?php

set_include_path(dirname(__FILE__) . PATH_SEPARATOR . dirname(__FILE__) . '/lib/');

if(file_exists(dirname(__FILE__) . '/setup.override.php'))
  require_once(dirname(__FILE__) . '/setup.override.php');

require_once('limb/core/common.inc.php');
require_once('limb/cms/common.inc.php');

lmb_env_setor('LIMB_VAR_DIR', dirname(__FILE__) . '/var/');
EOD;

  $root = taskman_prop('PROJECT_DIR');
  file_put_contents($root.'/src/LimbApplication.class.php', $application);
  file_put_contents($root.'/setup.php', $setup);
}

/**
 * @desc Create folders in /www/shared for all packages
 */
function task_project_shares()
{
  foreach(glob(taskman_prop('PROJECT_DIR')."/lib/limb/*/shared") as $pkg_shared)
  {
    $pkg = basename(dirname($pkg_shared));

    $shared_dir = taskman_prop('PROJECT_DIR') . '/www/shared/';
    if(!is_dir($shared_dir)) mkdir($shared_dir,0755,true);
    $destination = $shared_dir . $pkg;

    if(is_link($destination))
      unlink($destination);
    else
      lmbFs::rm($destination);

    if(function_exists('symlink'))
    {
      symlink($pkg_shared, $destination);
      taskman_msg("Created symlink for $pkg ($destination)...\n");
    }
    else
    {
      lmbFs::cp($pkg_shared, $destination);
      taskman_msg("Copied share for $pkg ($destination)...\n");
    }
  }
}

/**
 * @desc Create var folder
 */
function task_project_var_dir()
{
  $var_path = taskman_prop('PROJECT_DIR') . '/var';
  if(file_exists($var_path))
  {
    taskman_msg("Var dir ($var_path) exists...\n");
    return;
  }
  lmbFs::mkdir($var_path);
  taskman_msg("Created var dir ($var_path)...\n");
}

/**
 * @desc Init db
 * @deps project_db_create
 * @example project.php init_db -D DSN=mysqli://root:test@localhost/limb_app?charset=utf8
 */
function task_project_db_load($argv)
{
  require_once('limb/dbal/src/lmbDbDump.class.php');

  $type = lmbToolkit :: instance()->getDefaultDbConnection()->getType();
  $dump_file = taskman_prop('PROJECT_DIR') . '/lib/limb/'.taskman_propor('INIT_PACKAGE','cms'). '/init/db.' . $type;
  $dump = new lmbDbDump($dump_file);
  $dump->load();
  taskman_msg("Dump ($dump_file) loaded...\n");
}

/**
 * @desc create db by specified DSN
 * @deps project_db_init_config,project_setup_project
 * @example project.php create_db -D DSN=mysqli://root:test@localhost/limb_app?charset=utf8
 */
function task_project_db_create($args)
{
  require_once('limb/dbal/src/lmbDbDump.class.php');

  $toolkit = lmbToolkit :: instance();
  $fake_dsn = clone($toolkit->getDefaultDbDSN());
  $db_name = $fake_dsn->database;
  $fake_dsn->database = false;
  $conn = $toolkit->createDbConnection($fake_dsn);
  lmbDBAL::execute('CREATE DATABASE '.$conn->quoteIdentifier($db_name), $conn);
  taskman_msg("Database ($db_name) created...\n");
}

/**
 *@desc Init db config by given DSN param
 *@example project.php init_db_config -D DSN=sqlite:///www/skel/db/database.sqlite
 */
function task_project_db_init_config()
{
  lmb_package_require('dbal');

  $config_file = taskman_prop('PROJECT_DIR').'/settings/db.conf.php';
  if(file_exists($config_file))
    if(!lmb_cli_ask_for_accept('Database config exists. Overwrite it?'))
      return;

  if(!$dsn_str = taskman_propor('DSN', ''))
    $dsn_str = lmb_cli_ask_for_option('Dsn');

  lmb_require('limb/dbal/src/lmbDbDSN.class.php');
  $dsn = new lmbDbDSN($dsn_str);

  $config_text = <<<EOD
<?php

\$conf = array('dsn' => '$dsn_str');
EOD;

  file_put_contents($config_file, $config_text);
  taskman_msg("DB config ($config_file) writed...Done\n");
}

function task_project_setup_project()
{
  require_once(taskman_prop('PROJECT_DIR') . '/setup.php');
}