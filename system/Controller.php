<?php

class Controller
{
    public $request;
    public $variables;

    public function __construct()
    {
        $this->request = new RequestHelper();
    }

    protected function goToAction($action)
    {
        global $System;

        if ($action) header('location: ' . _PATH_ . $System->getController() . '/' . $action);
        else header('location: ' . _PATH_ . $System->getController());
        exit;
    }

    protected function addVariable($name, $value)
    {
        $this->variables[$name] = $value;
    }

    protected function view($path, $variables = false)
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->view($p, $variables);
            }
        } else if (is_string($path)) {
            $extensions = ['html', 'php'];
            $file = null;

            foreach ($extensions as $ext) {
                $file_name = _DIR_VIEWS_ . $path . '.' . $ext;
                if (file_exists($file_name)) {
                    $file = $file_name;
                    break;
                }
            }

            if (!$file) {
                die('View "' . $path . '" nao encontrada');
            } else {
                $content = file_get_contents($file);

                if (is_array($this->variables)) {
                    foreach ($this->variables as $k => $v) {
                        $content = str_replace('[[:' . $k . ']]', $v, $content);
                    }
                }

                if (is_array($variables)) {
                    foreach ($variables as $k => $v) {
                        $content = str_replace('[[:' . $k . ']]', $v, $content);
                    }
                }

                echo $content;
            }
        }
    }
}
