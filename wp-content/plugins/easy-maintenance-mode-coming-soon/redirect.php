<?php
/*
        * Function  my_page_template_redirect
        *
        * To redirect our site url by coming soon page,after redirecting only our coming soon page can display
        *
        */
        function easy_maintenance_mode_template_redirect()
        {	
		
			//get page for live preview when my_preview is true,that mean when this page is call on live preview
			if (  (isset($_GET['easy_maintenance_mode_preview']) && $_GET['easy_maintenance_mode_preview'] == 'true') )
			{
				$file = plugin_dir_path( __FILE__ ).'/themes/index.php';
				include($file);
				exit();
			}
			//get status value from option table for enable or disable coming soon page
	        $value=get_option('easy_maintenance_mode_general_settings');

            //condition matching if status is not disable for coming soon then page is redirect
			if($value['status']!=='0')
            {
				header('HTTP/1.1 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 86400'); // retry in a day
				if(!is_feed())
				{
                	//if user not login page is redirect on coming soon template page
	            	if ( !is_user_logged_in()  )
                	{
                        //get path of our coming soon display page and redirecting
                    	$file =plugin_dir_path( __FILE__ ).'/themes/index.php';
	                	include($file);
			        	exit();
                	}
				}
                //if user is log-in then we check role.
	            if (is_user_logged_in() )
                {
                    //get logined in user role
		            global $current_user; /* Global variable already have the current user info so need to call get_currentuserinfo or wp_get_current_user */
		            // get_currentuserinfo(); /* Function is deprecated in the version 4.5 and above new function wp_get_current_user added */
		            $LoggedInUserID = $current_user->ID;
		            $UserData = get_userdata( $LoggedInUserID );
		            //if user role not 'administrator' redirect him to message page
		            if($UserData->roles[0] != "administrator")
		            {
                        if(!is_feed())
                        {
		                    $file =plugin_dir_path( __FILE__ ).'/themes/index.php';
	                       include($file);
			               exit();
		                }
                    }
		        }
            }
        }

        /*
        * add action to call function wbr_coming_soon_pro_template_redirect
        */
        add_action( 'template_redirect', 'easy_maintenance_mode_template_redirect' );       
?>