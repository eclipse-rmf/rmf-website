<?php
/*******************************************************************************
 * Copyright (c) 2009 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    
 *******************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . "/eclipse.org-common/system/app.class.php");	require_once($_SERVER['DOCUMENT_ROOT'] . "/eclipse.org-common/system/nav.class.php"); 	require_once($_SERVER['DOCUMENT_ROOT'] . "/eclipse.org-common/system/menu.class.php"); 	$App 	= new App();	$Nav	= new Nav();	$Menu 	= new Menu();		include($App->getProjectCommon());
	
	$localVersion = false;
	
	# Define these here, or in _projectCommon.php for site-wide values
	$pageKeywords	= "eclipse, project";
	$pageAuthor		= "Lukas Ladenberger";
	$pageTitle 		= "Requirements Modeling Framework - RMF";
	
	
	// # Header
	$html = file_get_contents('pages/_header.html');
	
	// 	# Paste your HTML content between the EOHTML markers!
	$html .= file_get_contents('pages/_download.html');
	
	$ordner = "http://download.eclipse.org/rmf/downloads/drops/0.1.0";
	$handle = opendir($ordner);
	while ($file = readdir ($handle)) {
	    if($file != "." && $file != "..") {
	        if(is_dir($ordner."/".$file)) {
	            $html .= "/".$file."<br/>";
	        } else {
	            // kompletter Pfad
	            $compl = $ordner."/".$file;
	            $html .= "<a href=\"".$compl."\">".$file."</a><br/>";
	        }
	    }
	}
	closedir($handle);
	
	// # Footer
	$html .= file_get_contents('pages/_footer.html');

	# Generate the web page
	$App->generatePage($theme, $Menu, null, $pageAuthor, $pageKeywords, $pageTitle, $html);

?>