<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\Apps;

  require('includes/application_top.php');

  $hooks = [];

  $directory = DIR_FS_CATALOG . 'includes/Module/Hooks/';

  if (file_exists($directory)) {
    if ($dir = new \DirectoryIterator($directory)) {
      foreach ($dir as $file) {
        if (!$file->isDot() && $file->isDir()) {
          $site = $file->getBasename();

          if ($sitedir = new \DirectoryIterator($directory . $site)) {
            foreach ($sitedir as $groupfile) {
              if (!$groupfile->isDot() && $groupfile->isDir()) {
                $group = $groupfile->getBasename();

                if ($groupdir = new \DirectoryIterator($directory . $site . '/' . $group)) {
                  foreach ($groupdir as $hookfile) {
                    if (!$hookfile->isDot() && !$hookfile->isDir() && ($hookfile->getExtension() == 'php')) {
                      $hook = $hookfile->getBasename('.php');

                      $class = 'OSC\OM\Module\Hooks\\' . $site . '\\' . $group . '\\' . $hook;

                      $h = new \ReflectionClass($class);

                      foreach ($h->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC) as $method) {
                        if ($method->name != '__construct') {
                          $hooks[$site . '/' . $group . '\\' . $hook][] = ['method' => $method->name];
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  foreach (Apps::getModules('Hooks') as $k => $v) {
    list($vendor, $app, $code) = explode('\\', $k, 3);

    $h = new \ReflectionClass($v);

    foreach ($h->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC) as $method) {
      if ($method->name != '__construct') {
        $hooks[$code][] = [
          'app' => $vendor . '\\' . $app,
          'method' => $method->name
        ];
      }
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<style>
.sitePill {
  color: #fff;
  background-color: #009933;
  border-radius: 20px;
  padding: 5px 10px;
}

.appPill {
  color: #fff;
  background-color: #0066CC;
  border-radius: 20px;
  padding: 5px 10px;
}
</style>

<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
    <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
  </tr>
</table>

<table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
  foreach ($hooks as $code => $data) {
    $counter = 0;

    foreach ($data as $v) {
      $counter++;

      list($site, $group) = explode('/', $code, 2);
?>

  <tr class="dataTableRow">

<?php
      if ($counter === 1) {
?>

    <td class="dataTableContent" style="padding: 10px;" <?php if (count($data) > 1) echo 'rowspan="' . count($data) . '"';?>><?php echo '<span class="sitePill">' . $site . '</span> ' . $group; ?></td>

<?php
      }
?>

    <td class="dataTableContent" style="padding: 10px;"><?php echo (isset($v['app']) ? '<span class="appPill">' . $v['app'] . '</span> ' : '') . $v['method']; ?></td>
  </tr>

<?php
    }
  }
?>

</table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
