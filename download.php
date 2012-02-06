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
	require_once($_SERVER["DOCUMENT_ROOT"] . "/modeling/includes/downloads-scripts.php");
	
	$localVersion = false;
	
	# Define these here, or in _projectCommon.php for site-wide values
	$pageKeywords	= "eclipse, project";
	$pageAuthor		= "Lukas Ladenberger";
	$pageTitle 		= "Requirements Modeling Framework - RMF";
	

	function getArtifacts($folder) {
		$ar = array();
		$aDirectory = dir($folder);
		while ($file = $aDirectory->read()) {
			if($file != "." && $file != "..") {
				$po = $folder."/".$file;
				if(!is_dir($po)) {
					$ar[] = $file;
				}
			}
		}	
		return $ar;
	}
	
	function getArtifactsFolder($folder)
	{
		$artifacts = array('Snapshot' => array(), 'Nightly' => array(), 'Integration' => array());
		$aDirectory = dir($folder);
		while ($file = $aDirectory->read()) {
			if($file != "." && $file != "..") {
				$po = $folder."/".$file;
				if(is_dir($po)) {
					$prefix = substr($file, 0, 1);
					if($prefix == 'S') {
						$artifacts['Snapshot'][$file] = getArtifacts($po);
					} elseif($prefix == 'N'){
						$artifacts['Nightly'][$file] = getArtifacts($po);
					} elseif($prefix == 'I') {
						$artifacts['Integration'][$file] = getArtifacts($po);
					}
				}
			}
		}
		return $artifacts;
	}
	
	function getVersions($folder) {
		$array = array();
		$aDirectory = dir($folder);
			while ($file = $aDirectory->read()) {
			if($file != "." && $file != "..") {
				if(is_dir($folder."/".$file)) {
					$array[$file] = getArtifactsFolder($folder."/".$file);
				}
			}
		}
		return $array;
	}
	
	function printArtifacts($folder) {
		
		$elements = getVersions($folder);
		
		$str = '';
		
		//Sort verion numbers
		krsort($elements);
		
		//Versionnumbers
		while($versions = current($elements)) {
			
			$version = key($elements);
			$str .= "<h4>".$version."<h4>\n";
	
			//Categories
			while($categories = current($versions)) {
	
				krsort($categories);
				$category = key($versions);
				$str .= "<h5>".$category."<h5>\n";
		
				$str .= "<ul>\n";
				$str .= "<li>Updatesite: <i>http://download.eclipse.org/rmf/updates/".strtolower($category)."/".$version."</i></li>\n";
				$str .= "</ul>\n";
				
				$str .= "<ul>\n";
					$show = 'none';
					if($category == 'Snapshot')
						$show = '';
					//Artifactsfolder
					while($afolders = current($categories)) {
						$afolder = key($categories);
						$str .= "<li><a href=\"javascript:toggle('".$afolder."')\">".$afolder." (".IDtoDateStamp($afolder, 0).")</a>\n";
						$str .= "<ul id='".$afolder."' style='display:".$show."'\>\n";
						//Artifacts
						while($artifact = current($afolders)) {
							$str .= "<li><a href='http://www.eclipse.org/downloads/download.php?file=/rmf/downloads/drops/".$version."/".$afolder."/".$artifact."'><img src='http://www.eclipse.org/modeling/images/dl.gif' style='border:0;' />".$artifact."</a></li>\n";
							next($afolders);
						}
						$str .= "</ul>\n";
						$str .= "</li>\n";
						$show = 'none';
						next($categories);
					}
					
				$str .= "</ul>\n";
	
				next($versions);
			}
			
			next($elements);
			
		}
		return $str;
	}
	
	// # Header
	$html = file_get_contents('pages/_header.html');
	
	$folder = $App->getDownloadBasePath()."/rmf/downloads/drops";

	$html .= printArtifacts($folder);

	// # Footer
	$html .= file_get_contents('pages/_footer.html');

	# Generate the web page
	$App->AddExtraHtmlHeader('<script src="/modeling/includes/downloads.js" type="text/javascript"></script>' . "\n"); //ie doesn't understand self closing script tags, and won't even try to render the page if you use one
	$App->generatePage($theme, $Menu, null, $pageAuthor, $pageKeywords, $pageTitle, $html);

?>