<?php
/**
* Plugin Name: WP Config File Editor
* Plugin URI: https://www.github.com/corsairh
* Text Domain: wp-config-file-editor 
* Domain Path: /Languages
* Author: AHMeD HAMeD
* Author URI: https://www.github.com/corsairh
* Version: 1.7.1
* Description: Modify Wordpress wp-config.php file values using a Simple User Interface Form, In additional is can be used to change wide system parameters
* License: GPL2
*/

# WCFE Namespace
namespace WCFE;

# Autoloads
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

# Constants
const NO_DIRECT_ACCESS_MESSAGE = 'Access Denied';

# Wordpres Plugin Framework
use WPPFW\Plugin\PluginBase;
use WPPFW\Plugin\Localization;

# Modules
use WCFE\Services;

/**
* 
* @author AHMeD SAiD 
*/
class Plugin extends PluginBase 
{
	
	/**
	* 
	*/
	const DIR = __DIR__;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	private $bindingHooks;
    
    /**
    * put your comment there...
    * 
    * @var Services\EditorModule
    */
    private $editorModule;

	/**
	* Holds ARV Plugin instance
	* 
	* @var Plugin
	*/
	private static $instance;
    
	/**
	* put your comment there...
	* 
	* @var Services\ProfilesModule
	*/
	private $profilesModule;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	private $sysFiltersModule;

    /**
    * put your comment there...
    * 
    * @param mixed $txt
    */
    public static function __( $txt )
    {
        return call_user_func_array( array( self::$instance->getExtension( 'l10n' ), '_' ), func_get_args() );
    }
    
	/**
	* Bootstrap ARV Plugin
	* 
	* return void
	*/
	protected function __construct() 
	{
		# Plugin base
		parent::__construct( __FILE__, new Config\Plugin() );	
	}

	/**
	* put your comment there...
	* 
	*/
	public function _initializePluggableHooks()
	{
		
		////////////////////////////////////#$
		
		do_action( Hooks::ACTION_PLUGIN_LOADED, $this );
		
		////////////////////////////////////#$
		
		
		////////////////////////////////////#$
		
		if ( $this->bindingHooks ) 
		{
			do_action( Hooks::ACTION_PLUGIN_BINDING_HOOKS, $this );	
		}
		
		////////////////////////////////////#$	
	}
	
    /**
    * put your comment there...
    * 
    */
    public function _localize()
    {
        if (!$this->getExtension('l10n'))
        {
            // Load localization extension
            $this->setExtension(
                'l10n', 
                Localization::localize(basename(dirname(__FILE__)), $this)
            );            
        }

    }
    
	/**
	* put your comment there...
	* 
	*/
	protected function bootStrap()
	{
        
		# Wordpress backward compatibility
		CompatibleWordpress::loadCompatibilityLayers( $GLOBALS[ 'wp_version' ] );
		
		# Installer
		if ( ! \WCFE\Installer\Installer::run( ) )
		{
			// Could not install !!!!
			return;
		}
		
		# System Plugins
		\WCFE\SysPlugins\Plugins::load()->runPlugins();
		
        # Localization
        add_action('plugins_loaded', array($this, '_localize'));
        
		# MVC components
		# Only admin side is used in this Plugin
		if ( is_admin() ) 
		{
			
			# Editor Module
			$this->editorModule = new Services\EditorModule( $this );
			$this->editorModule->start();
			
			# Profile module
			$this->profilesModule = new Services\ProfilesModule( $this );
			
			# System filters module
			$this->sysFiltersModule = new Services\SysFiltersModule( $this );
			$this->sysFiltersModule->start();
            
			# Profiles is totally relying on AJAX
			# editor Ajax should be here too however I will do that
			# in the subsequence releases
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			{
				$this->profilesModule->start();
			}
			
			$this->bindingHooks = true;
			
		}

		# Start using hooks at this point so that WCFE Extensions can get involved
		add_action( 'plugins_loaded', array( $this, '_initializePluggableHooks' ) );
			
	}

	/**
	* put your comment there...
	* 
	*/
	public function & getEditorModule()
	{
		return $this->editorModule;
	}

	/**
	* put your comment there...
	* 
	*/
	public function & getProfilesModule()
	{
		return $this->profilesModule;
	}
	
	/**
	* put your comment there...
	* 
	*/
	public function & getSysFiltersModule()
	{
		return $this->sysFiltersModule;
	}
	
	/**
	* put your comment there...
	* 
	*/
	public static function & me()
	{
		return self::$instance;
	}
    
    /**
    * put your comment there...
    * 
    */
    protected function onCreateServiceFront()
    {
        $this->_localize();
    }
    
	/**
	* Run ARV Plugin if not alreayd running
	* 
	* This methos is to construct a new ARV\Plugin instance if not already
	* instantiated.
	* 
	* @return PLugin
	*/
	public static function plug()
	{
		# Create if not yet created
		if ( ! self::$instance )
		{
			# Get Instance
			self::$instance = new Plugin();
		
			# Load
			self::$instance->bootStrap();
		}
		
		# Return instance
		return self::$instance;
		
	}

}

# Run The Plugin
Plugin::plug();

