<?php

/*
 * Michael Zijlstra 03 May 2017
 * 
 * It would be good to create proper documentation for this class
 */

class AnnotationReader {
    public $mappings = array();
    public $repositories = array();
    public $controllers = array();
    public $context = "";

    /**
     * Constructor, initiallizes internal mappings arrays
     */
    public function __construct() {
        $this->mappings["GET"] = array();
        $this->mappings["POST"] = array();
    }

    /**
     * Helper function to extract annotation attributes (key / value pairs)
     * 
     * @param string $annotation
     * @param string $text
     * @return array
     * @throws Exception
     */
    private function annotation_attributes($annotation, $text) {
        $matches = array();
        preg_match("#" . $annotation . "\((.*)\)#", $text, $matches);
        $content = $matches[1];
        if (preg_match("#^['\"].*['\"]$#", $content)) {
            $content = "value=" . $content;
        }
        $result = array();
        if (!$attrs = preg_split("#,\s*#", $content)) {
            $attrs = array($content);
        }
        foreach ($attrs as $attr) {
            if (!preg_match("#(\w+)\s*=\s*['\"](.*?)['\"]#", $attr, $matches)) {
                throw new Exception("Malformed annotation attribute in "
                . $annotation . " found in " . $text);
            }
            $key = $matches[1];
            $value = $matches[2];
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Checks the properties of a class for @Inject annotations
     * 
     * @param type $reflect_class
     * @return type
     */
    private function to_inject($reflect_class) {
        $result = array();
        foreach ($reflect_class->getProperties() as $prop) {
            $com = $prop->getDocComment();
            if (preg_match("#@Inject#", $com)) {
                $attrs = $this->annotation_attributes("@Inject", $com);
                $result[$prop->getName()] = $attrs["value"];
            }
        }
        return $result;
    }

    /**
     * Validate the content of @GET and @POST annotations
     * 
     * @param type $attrs
     * @param type $com
     * @global type $SEC_LVLS
     * @throws Exception
     */
    private function validate_request_annotation(&$attrs, $com) {
        global $SEC_LVLS;
        if (isset($attrs['uri']) == false && isset($attrs['value']) == false ) {
			print("has url: " . (isset($attrs['url']) == true)); 
			var_dump($attrs);
            throw new Exception("@GET or @POST missing uri attribute in: $com");
        }
        if (!isset($attrs['uri']) && isset($attrs['value'])) {
            $attrs['uri'] = $attrs['value'];
        }
        if (!isset($attrs['sec'])) {
            $attrs['sec'] = "none";
        }
        if (!in_array($attrs["sec"], $SEC_LVLS)) {
            throw new Exception("Bad sec value in @GET or @POST found in: $com");
        }
    }

    /**
     * 
     * @param Reflection_Class $reflect_class
     * @param string $req GET or POST
     * @param string $type ctrl or ws
     */
    private function map_requests($reflect_class, $req) {
        foreach ($reflect_class->getMethods() as $m) {
            $com = $m->getDocComment();
            $match = array();

            preg_match_all("#@{$req}\(.*\)#", $com, $match);
            foreach ($match[0] as $a) {
                $attrs = $this->annotation_attributes("@{$req}", $a);
                $this->validate_request_annotation($attrs, $a);
                $method_loc = $reflect_class->getName() . "@" . $m->getName();
                $mapping = ["sec"=> $attrs["sec"], "route" => $method_loc];
                $this->mappings[$req][$attrs["uri"]] = $mapping;
            }
        }
    }

    /**
     * Checks if a class has an @Repository annotation, if it does adds it to
     * the array of repositories
     * 
     * @param type $class
     */
    private function check_repository($class) {
        $r = new ReflectionClass($class);
        $doc = $r->getDocComment();
        if (preg_match("#@Repository#", $doc)) {
            $to_inject = $this->to_inject($r);
            $this->repositories[$class] = $to_inject;
        }
    }

    /**
     * Checks if classes have a @Controller or a @WebService annotation, and
     * the processes them as needed
     * 
     * @param type $class
     */
    private function check_controller($class) {
        $r = new ReflectionClass($class);
        $doc = $r->getDocComment();
        if (preg_match("#@Controller#", $doc) ||
                preg_match("#@WebService#", $doc)) {
            $to_inject = $this->to_inject($r);
            $this->controllers[$class] = $to_inject;
            $this->map_requests($r, "GET");
            $this->map_requests($r, "POST");
        }
    }

    /**
     * Scan for PHP classes in a directory, calling the passed function on them
     * 
     * @param string $directory
     * @param function $function
     */
    private function scan_classes($directory, $function) {
        $files = scandir($directory);
        foreach ($files as $file) {
            $mats = array();
            // skip hidden files, directories, files that are not .class.php
            if ($file[0] === "." || is_dir($file) ||
                    !preg_match("#(.*)\.class\.php#i", $file, $mats)) {
                continue;
            }
            $this->{$function}($mats[1]);
        }
    }

    /**
     * Generate code for the retrievable classes to the context class
     * 
     * @param type $classes
     */
    private function add_classes_to_context($classes) {
        foreach ($classes as $class => $injects) {
            $this->context .= <<< IF_START
        if (\$id === "$class" && !isset(\$this->objects["$class"])) {
            \$this->objects["$class"] = new $class();

IF_START;
            foreach ($injects as $prop => $id) {
                $this->context .=
                        "            \$this->objects[\"$class\"]->$prop = "
                        . "\$this->get(\"$id\");\n";
            }
            $this->context .= "        }\n"; // close if statement
        }
    }

    /**
     * Scans the standard directories, reading .php files for annotations
     * 
     * @return AnnotationContext self for call chaining
     */
    public function scan() {
        $this->scan_classes("model", "check_repository");
        $this->scan_classes("control", "check_controller");
        return $this;
    }

    /**
     * Creates the context in memory
     * 
     * @return AnnotationContext self for call chaining
     */
    public function create_context() {
        $this->context .= <<< WARNING
/******************************************************************************* 
 * DO NOT MODIFY THIS FILE, IT IS GENERATED 
 * 
 * When DEVELOPMENT=true this file is generated based on the settings in 
 * frontController.php and the annotations found in the class files in the 
 * control and model directories
 ******************************************************************************/

WARNING;
        // generate the mappings array
        $this->context .= "\$mappings = array(\n";
        foreach ($this->mappings as $method => $items) {
            $this->context .= "\t\"$method\" => array(\n";
            foreach ($items as $uri => $mapping) {
                $sec = $mapping['sec'];
                $route = $mapping['route'];
                $this->context .= "\t\t'$uri' => \n\t\t\t";
                $this->context .= "['sec' => '$sec', 'route' => '$route'],\n";
            }
            $this->context .= "\t),\n";
        }
        $this->context .= ");\n";

        // these values are set in frontController.php
        $dsn = DSN;
        $user = DB_USER;
        $pass = DB_PASS;

        $this->context .= <<< HEADER
class Context {
    private \$objects = array();
    
    public function __construct() {
        \$db = new PDO("$dsn", "$user", "$pass");
        \$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        \$this->objects["DB"] = \$db;
    }

    public function get(\$id) {

HEADER;

        $this->add_classes_to_context($this->repositories);
        $this->add_classes_to_context($this->controllers);

        $this->context .= <<< FOOTER
        return \$this->objects[\$id];
    } // close get method
} // close Context class
FOOTER;

        return $this;
    }

    /**
     * Writes the context (as found by scan) to a file
     * 
     * @param string $filename
     * @return AnnotationContext self for call chaining
     */
    public function write($filename) {
        $data = "<?php\n" . $this->context;
        file_put_contents($filename, $data);
        return $this;
    }

}
