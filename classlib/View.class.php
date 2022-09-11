<?php

class View {

    public $file_path;
    public $layout_path;
    public $styles; 
    public $scripts;

    public function __construct(
        string $file_path,
        string $layout_path = null,
        array $styles = [],
        array $scripts = [],
        bool $use_default_layout = true,
        $definitions = [],
        array $layout_options = []
    ) {
        $this->defineArray($definitions);
        $this->layout_path = $layout_path;
        $this->file_path   = $file_path;
        $this->styles      = $styles;
        $this->scripts    = $scripts;
    }

    public function render() {
        /** @var Config $config */
        global $config;
        ob_clean();

        foreach ($this->definitions as $name => $value) {
            ${$name} = $value;
        }

        if (is_null($this->layout_path)) {            
            if (file_exists($this->file_path)) {
                include $this->file_path;
                return;
            }            
        }

        if (file_exists($this->layout_path)) {

            echo '<html><head>';
            include __DIR__ . "/../views/header.html";
            
            $this->renderStyles(null);
            $this->renderScripts(null);
            echo '<body>';
            include $this->layout_path;
            echo '</body></head>';            
            return;

        }
        
        include "views/special/404.view.php";

    }

    public function renderStyles(?array $styles = null) {
        /** @var Config $config */
        global $config;

        if (is_null($styles)) {
            $styles = $this->styles;
        }
        
        foreach ($styles as $style) {             
            echo "<link rel=\"stylesheet\" href=\"{$style}\" />";
        }
         

        return $this;
    }

    public function renderScripts(?array $scripts = null) {
        /** @var Config $config */
        global $config;

        if (is_null($scripts)) {
            $scripts = $this->scripts;
        }

        
        foreach ($scripts as $script) {
            echo "<script src=\"{$script}\"></script>";
        }

        return $this;
    }

    public function getMinFilePath(string $file_path): string {
        $parts     = explode(".", $file_path);
        $extension = $parts[array_key_last($parts)];

        array_pop($parts);

        return join(  ".",  $parts) . ".min." . $extension;
    }

    protected $definitions = [];

    public function define(string $name, $value) {
        $this->definitions[$name] = $value;
        return $this;
    }

    public function defineArray(array $definitions) {
        map($definitions, fn ($key, $value) => $this->define($key, $value));
        return $this;
    }
}
