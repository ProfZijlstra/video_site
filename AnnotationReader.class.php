<?php

/**
 * Michael Zijlstra 03 May 2017
 *
 * Refactoring to use PHP Attributes instead of doc comment annotations
 * mzijlstra 2024 Jan 09
 */

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    public string $path;
    public function __construct(string $path = "")
    {
        $this->path = $path;
    }
}

#[Attribute(Attribute::TARGET_CLASS)]
class Repository
{
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Inject
{
    public string $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Request
{
    public string $method;
    public string $uri;
    public string $sec;
    public function __construct(string $method, string $uri = "", string $sec = "none")
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->sec = $sec;
    }
    public function __toString(): string
    {
        return $this->method . " " . $this->uri . " " . $this->sec;
    }
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Get extends Request
{
    public function __construct(string $uri, string $sec = "none")
    {
        parent::__construct('GET', $uri, $sec);
    }
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Post extends Request
{
    public function __construct(string $uri, string $sec = "none")
    {
        parent::__construct('POST', $uri, $sec);
    }
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Put extends Request
{
    public function __construct(string $uri, string $sec = "none")
    {
        parent::__construct('PUT', $uri, $sec);
    }
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Delete extends Request
{
    public function __construct(string $uri, string $sec = "none")
    {
        parent::__construct('DELETE', $uri, $sec);
    }
}

class AnnotationReader
{
    private array $REQ_TYPES = ["GET", "POST", "PUT", "DELETE"];
    public array $mappings = array();
    public array $repositories = array();
    public array $controllers = array();
    public string $context = "";

    /**
     * Constructor, initiallizes internal mappings arrays
     */
    public function __construct()
    {
        foreach ($this->REQ_TYPES as $req) {
            $this->mappings[$req] = array();
        }
    }

    /**
     * Checks the properties of a class for Inject attributes
     * 
     * @param type $reflect_class
     * @return type
     */
    private function to_inject(ReflectionClass $reflect_class): array
    {
        $result = array();
        foreach ($reflect_class->getProperties() as $prop) {
            $attrs = $prop->getAttributes();
            foreach ($attrs as $attr) {
                if ($attr->getName() == "Inject") {
                    $result[$prop->getName()] = $attr->getArguments()[0];
                }
            }
        }
        return $result;
    }

    /**
     * Validate the content of a Request annotation
     * 
     * @param Request $request
     * @global type $SEC_LVLS
     * @throws Exception
     */
    private function validate_request_annotation(Request $request): void
    {
        global $SEC_LVLS;
        if (!in_array($request->method, $this->REQ_TYPES)) {
            throw new Exception("Bad request attribute method value in: $request");
        }
        if ($request->uri == "") {
            throw new Exception("Request attribute missing uri in: $request");
        }
        if (!in_array($request->sec, $SEC_LVLS)) {
            throw new Exception("Bad sec value in @GET or @POST found in: $request");
        }
    }

    /**
     * Maps Request annotations to the internal mappings array
     
     * @param Reflection_Class $reflect_class
     */
    private function map_requests(ReflectionClass $reflect_class, string $path): void
    {
        foreach ($reflect_class->getMethods() as $m) {
            $attrs = $m->getAttributes();
            foreach ($attrs as $a) {
                $req = $a->newInstance();
                if (!($req instanceof Request)) {
                    continue;
                }
                $this->validate_request_annotation($req);
                $method_loc = $reflect_class->getName() . "@" . $m->getName();
                $mapping = ["sec" => $req->sec, "route" => $method_loc];
                $uri = "!" . $path . $req->uri . "!";
                $this->mappings[$req->method][$uri] = $mapping;
            }
        }
    }

    /**
     * Checks if a class has an Repository annotation, if it does adds it to
     * the array of repositories
     * 
     * @param string $class
     */
    private function check_repository($class)
    {
        $r = new ReflectionClass($class);
        $attrs = $r->getAttributes();
        foreach ($attrs as $a) {
            if ($a->getName() == "Repository") {
                $to_inject = $this->to_inject($r);
                $this->repositories[$class] = $to_inject;
            }
        }
    }

    /**
     * Checks if classes have a Controller annotation, and
     * processes them as needed
     * 
     * @param string $class
     */
    private function check_controller($class)
    {
        $r = new ReflectionClass($class);
        $attrs = $r->getAttributes();
        foreach ($attrs as $a) {
            if ($a->getName() == "Controller") {
                $to_inject = $this->to_inject($r);
                $this->controllers[$class] = $to_inject;
                $path = $a->newInstance()->path;
                $this->map_requests($r, $path);
            }
        }
    }

    /**
     * Scan for PHP classes in a directory, calling the passed function on them
     * 
     * @param string $directory
     * @param function $function
     */
    private function scan_classes($directory, $function)
    {
        $files = scandir($directory);
        foreach ($files as $file) {
            $mats = array();
            // skip hidden files, directories, files that are not .class.php
            if (
                $file[0] === "." || is_dir($file) ||
                !preg_match("#(.*)\.class\.php#i", $file, $mats)
            ) {
                continue;
            }
            $this->{$function}($mats[1]);
        }
    }

    /**
     * Scans the standard directories, reading .php files for annotations
     * 
     * @return AnnotationContext self for call chaining
     */
    public function scan()
    {
        $this->scan_classes("model", "check_repository");
        $this->scan_classes("control", "check_controller");
        return $this;
    }

    /**
     * Generate code for the retrievable classes to the context class
     * 
     * @param type $classes
     */
    private function add_classes_to_context($classes)
    {
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
     * Creates the context in memory
     * 
     * @return AnnotationContext self for call chaining
     */
    public function create_context()
    {
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
    public function write($filename)
    {
        $data = "<?php\n" . $this->context;
        file_put_contents($filename, $data);
        return $this;
    }
}
