<?php
/**
 */
#ini_set('display_errors', 'on');
defined('_JEXEC') or die;

/**
 * CKEditor Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.ckeditor
 * @since       1.5
 */
class PlgEditorCKEditorBasic extends JPlugin
{
	/**
	 * Base path for editor files
	 */
	protected $_basePath = 'plugins/editors/ckeditorbasic';
    
    
    
    public $called = false;

    
    /**
	 * Constructor
	 *
	 * @param  object  $subject  The object to observe
	 * @param  array   $config   An array that holds the plugin configuration
	 *
	 * @since       1.5
	 */
	/*public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}*/

    /**
	 * Initialises the Editor.
	 *
	 * @return  string  JavaScript Initialization string
	 *
	 * @since 1.5
	 */
	public function onInit() {
        $document = JFactory::getDocument();
        // This loads all the necessary JS for the Media viewer. It's not great that it's here, 
        // because it's only used by the jimage plugin. However, there's nowhere else to put PHP
        // since all CKEditor plugins are JS based.
        // Mediafield required JQuery
        
        $app = JFactory::getApplication();
        
        if (!$app->isAdmin()) {
            $document->addScript(JUri::root() . 'media/jui/js/jquery.min.js');
            JHtml::_('script', 'media/mediafield.min.js', array('version' => 'auto', 'relative' => true));
        }
        #echo 'here'; exit;
        
		//$document->addStyleSheet(JUri::root() . $this->_basePath . '/ckeditor.css');
        $script = array();
        if (strpos(JPATH_BASE, 'administrator') === false) {
            $script[] =  "\n\n";
            $script[] = '    var ready = function(fn) {';
            $script[] = '        if (document.attachEvent ? document.readyState === "complete" : document.readyState !== "loading") {';
            $script[] = '            fn();';
            $script[] = '        } else {';
            $script[] = '            document.addEventListener(\'DOMContentLoaded\', fn);';
            $script[] = '        }';
            $script[] = '    }';
            $script[] =  "\n\n";
            $script[] = '    ready(function() {';
            $script[] = '        var editors_i = 0;';
            $script[] = '        var editors_l = editors.length;';
            $script[] = '        for (editors_i; editors_i < editors_l; editors_i++) {';
            $script[] = '            CKEDITOR.replace(editors[editors_i], {   ';
            $script[] = '                customConfig: \'customConfig.js\',';
            $script[] = '            });';
            $script[] = '        }';
            $script[] = '    })';
        }
        
        $script = implode("\n", $script);
            
        $script .= file_get_contents(__DIR__ . '/setup.js');
        $document->addScriptDeclaration($script);
        
        
        $document->addScript(JUri::root() . $this->_basePath . '/ckeditor/ckeditor.js');
        $document->addScript(JUri::root() . $this->_basePath . '/jplugins.js');
        // Add sticky js
        #$document->addScript(JUri::root() . 'js/vendor/sticky.js');
        // Add any custom JS files:
        $js_files = $this->params->get('jsfiles', '');
        
        //$js_files = '__DIR__/ck_wym/ck_wym.js';
        //$js_files = '__DIR__/ck_extras/ck_extras.js';
        
        //__DIR__/ck_wym/ck_wym.js __DIR__/ck_extras/ck_extras.js
        //echo '<pre>'; var_dump($js_files); echo '</pre>';exit;
        //echo '<pre>'; var_dump(JPATH_BASE); echo '</pre>';exit;
        //return '';
        if (!empty($js_files)) {
            $js_files = explode("\n", str_replace("\r", '', $js_files));
            foreach ($js_files as $file) {                
                if ($file = realpath(str_replace('__DIR__', __DIR__, $file))) {
                    $file = str_replace(array(JPATH_ROOT, '//', ':/'), array(JUri::root(), '/', '://'), $file);
                    $document->addScript($file);
                }
            }
        }
        
        return '';
    }
    
    /**
	 * Display the editor area.
	 *
	 * @param   string   $name     The name of the editor area.
	 * @param   string   $content  The content of the field.
	 * @param   string   $width    The width of the editor area.
	 * @param   string   $height   The height of the editor area.
	 * @param   int      $col      The number of columns for the editor area.
	 * @param   int      $row      The number of rows for the editor area.
	 * @param   boolean  $buttons  True and the editor buttons will be displayed.
	 * @param   string   $id       An optional ID for the textarea. If not supplied the name is used.
	 * @param   string   $asset    The object asset
	 * @param   object   $author   The author.
	 *
	 * @return  string
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array()) {
        #echo '<pre>'; var_dump(func_get_args()); echo '</pre>';exit;
        $return = '';
        require('js-vars.php');
        
        //$jplugins_path = JUri::root() . $this->_basePath . '/plugins';
        
        if ((int) $width) {
			$width .= 'px';
		}
		if ((int) $height) {
			$height .= 'px';
		}
		$return .= '<textarea name="'.$name.'" id="'.$id.'" cols="'.$col.'" rows="'.$row.'" style="width:'.$width.'; height:'.$height.'">' . $content . '</textarea>' . "\n";
		$return .= $this->_displayButtons($id, $buttons, $asset, $author);
        
        $return .= "<script type=\"text/javascript\">\n";
        #echo '<pre>'; var_dump(get_defined_constants(true)); echo '</pre>';exit;
        if (strpos(JPATH_BASE, 'administrator') !== false) {
            $script = '';
            /*foreach ($vars as $name => $value) {
                $script .= "var $name = $value;\n";
            }*/
            $script .= "    CKEDITOR.timestamp='202010080818';
        jQuery(function() {
        CKEDITOR.replace('" . $name . "', {   
            customConfig: 'customConfig.js',
        });
    });";
        } else {
            $script = '';
            foreach ($vars as $name => $value) {
                $script .= "if (typeof(editors) == 'undefined') {var editors = [];}\n
editors.push(" . $value . ");\n";
            }
        }
        $return .= $script;
        $return .= "</script>\n";
        return $return;
    }
    
	/**
	 * Get the editor content
	 *
	 * @param   string  $editor  The name of the editor
	 *
	 * @return  string
	 */
	public function onGetContent($editor) {
        return " CKEDITOR.instances.$editor.getData(); ";
	}

	/**
	 * Set the editor content
	 *
	 * @param   string  $editor  The name of the editor
	 * @param   string  $html    The html to place in the editor
	 *
	 * @return  string
	 */
	public function onSetContent($editor, $html) {
		return " CKEDITOR.instances.$editor.setData($html); ";
	}

	/**
	 * Copy editor content to form field
	 *
	 * @param   string  $editor  The name of the editor
	 *
	 * @return  string
	 */
	public function onSave($editor) {
		return '';
		//return 'if (tinyMCE.get("' . $editor . '").isHidden()) {tinyMCE.get("' . $editor . '").show()}; tinyMCE.get("' . $editor . '").save();';
	}

	/**
	 * Inserts html code into the editor
	 *
	 * @param   string  $name  The name of the editor
	 *
	 * @return  boolean
	 */
	public function onGetInsertMethod($name) {
        $document =  JFactory::getDocument();
        
        $url = str_replace('administrator/', '', JURI::base() );
		$js = "
            function IeCursorFix()
			{
				/* 
                This function is called onclick set on buttons in: /layouts/joomla/editors/buttons/button.php
                Need to the editor with IE to see if I need to implement anything here.
                */
				return true;
			}
            
            function jInsertEditorText(text, editor) {
                text = text.replace( /<img src=\"/, '<img src=\"".$url."' );
                //console.log(CKEDITOR.instances[editor]);
                //console.log(text);
                CKEDITOR.instances[editor].insertHtml(text);
           }";
		$document->addScriptDeclaration($js);
        
		return true;
	}
    
    /**
	 * Displays the editor buttons.
	 *
	 * @param   string  $name     The editor name
	 * @param   mixed   $buttons  [array with button objects | boolean true to display buttons]
	 * @param   string  $asset    The object asset
	 * @param   object  $author   The author.
	 *
	 * @return  string HTML
	 */
	private function _displayButtons($name, $buttons, $asset, $author) {
		$return = '';

		$args = array(
			'name'  => $name,
			'event' => 'onGetInsertMethod'
		);

		$results = (array) $this->update($args);

		if ($results) {
			foreach ($results as $result) {
				if (is_string($result) && trim($result)) {
					$return .= $result;
				}
			}
		}

		if (is_array($buttons) || $buttons === true) {
			$buttons = $this->_subject->getButtons($name, $buttons, $asset, $author);

			$return .= JLayoutHelper::render('joomla.editors.buttons', $buttons);
		}

		return $return;
	}

}
