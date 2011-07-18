<?php
/*
 * This file is part of the sfAltumoPlugin library.
 *
 * (c) Steve Sperandeo <steve.sperandeo@altumo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Handles all installed Git hooks by passing the call onto 
 * \sfAltumoPlugin\Build\GitHookHandler::handle()
 * 
 * Alternatively, installs the git hooks within git if hook-name is "install".
 * 
 * @author Steve Sperandeo <steve.sperandeo@altumo.com>
 */
class sfAltumoGitHookHandlerTask extends sfAltumoBaseTask {

    /**
    * @see sfTask
    */
    protected function configure() {
        
        parent::configure();
        
        $this->addArguments(array(
            new sfCommandArgument( 'hook-name', sfCommandArgument::REQUIRED, 'The git hook name ("install" to install hooks).' ),
        ));

        $this->addOptions(array(
            //new sfCommandOption( 'build', null, sfCommandOption::PARAMETER_OPTIONAL, 'Modifies an existing database according to available build files.', null ),            
        ));

        $this->name = 'git-hook-handler';

        $this->briefDescription = 'Calls the altumo git hook handler.';

        $this->detailedDescription = <<<EOF
Calls the altumo git hook handler. Alternatively, installs the git hooks within git if hook-name is "install".
EOF;
    }

    
   /**
   * @see sfTask
   */
    protected function execute( $arguments = array(), $options = array() ) {

        $hook_name = $arguments['hook-name'];
        $general_hook_template_file = sfConfig::get( 'sf_root_dir' ) . '/plugins/sfAltumoPlugin/lib/Build/git_hook_handler.sh';
        //$general_hook_template_file_contents = file_get_contents($general_hook_template_file);
        
        if( $hook_name == 'install' ){
            
            $target_path = realpath( sfConfig::get( 'sf_root_dir' ) . '/../../.git' ) . '/';
            
            $targets = array(
                'post-commit'
            );
            
            //check that all targets don't exist (just to be safe)
                foreach( $targets as $target ){
                    
                    $target_filename = $target_path . $target;
                    if( file_exists($target_filename) ){
                        sfCommandException( sprintf('Hook "%s" is already installed. Please remove it first.', $target_filename) );
                    }                    
                       
                }
                if( !is_writable($target_path) ){
                    sfCommandException( sprintf('"%s" is not writable. Please ensure this user can write to that directory.', $target_path) );
                }
                
            
            foreach( $targets as $target ){
                
                $target_filename = $target_path . $target;
                copy( $general_hook_template_file, $target_filename );
                chmod( $target_filename, 0755 );                
                                   
            }
            
        }else{
            
            \sfAltumoPlugin\Build\GitHookHandler::handle( $hook_name );
            
        }     
        
        
        
    }
    
}