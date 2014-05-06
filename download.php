<?php
/*******************************************************************************
 * Copyright (c) 2009 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Lukas Ladenberger
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
					$osar = getOsArtifact($file);
					$ar[] = array(
						'Name' => $file,					
						'Size' => filesize($po),
						'OS' => $osar['OS'],
						'Arch' => $osar['Arch']
						);
				}
			}
		}	
		$aDirectory->close();
		return $ar;
	}
	
	function getOsArtifact($artifactname) {
		$ar = array();
		$osarray = array(
			'x.gtk' => 'Linux',
			'win32' => 'Windows',
			'cocoa' => 'Mac OS X (Cocoa)'
		);
		$str = '';
		$arch = substr($artifactname,-10,-4);
		if($arch == 'x86_64') {
			$ar['OS'] = $osarray[substr($artifactname,-16,-11)];
			$ar['Arch'] = '64 Bit';
		} else {
			$ar['OS'] = $osarray[substr($artifactname,-13,-8)];
			$ar['Arch'] = '32 Bit';
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
		$aDirectory->close();
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
		$aDirectory->close();
		return $array;
	}
	
	function human_filesize($bytes, $decimals = 2) {
	  $sz = 'BKMGTP';
	  $factor = floor((strlen($bytes) - 1) / 3);
	  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}	
	
	function cmp($a, $b)
	{
	    return strcmp($a["OS"], $b["OS"]);
	}
	
	function printArtifacts($folder) {
		
		$elements = getVersions($folder);
		
		$str = '';
		
		//Sort verion numbers
		krsort($elements);

		$show = '';
		
		//Versionnumbers
		while($versions = current($elements)) {
			
			$version = key($elements);
			$str .= "<h4>".$version."<h4>\n";
			$str .= "<hr>\n";

			//Categories
			while($categories = current($versions)) {
	
				krsort($categories);
				$category = key($versions);
				$str .= "<h5>".$category."<h5>\n";
		
				//$str .= "<ul>\n";
				//$str .= "<li>Updatesite: <i>http://download.eclipse.org/rmf/updates/".strtolower($category)."/".$version."</i></li>\n";
				//$str .= "</ul>\n";
				
				$str .= "<ul>\n";

				//Artifactsfolder
				while($afolders = current($categories)) {
					$afolder = key($categories);
					usort($afolders, "cmp");
					$str .= "<li><a href=\"javascript:toggle('".$afolder."')\">".$afolder." (".IDtoDateStamp($afolder, 0).")</a>\n";
					$str .= "<ul id='".$afolder."' style='list-style:none;display:".$show."'\>\n";
					//Artifacts
					while($artifact = current($afolders)) {
						$str .= "<li><a href='http://www.eclipse.org/downloads/download.php?file=/rmf/downloads/drops/".$version."/".$afolder."/".$artifact['Name']."'><img src='http://www.eclipse.org/modeling/images/dl.gif' style='border:0;' /> ".$artifact['OS']." ".$artifact['Arch']." (".human_filesize($artifact['Size']).")</a></li>\n";
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

	$html .= "<h3>Software Repository</h3>\n";
	$html .= "<p>This project maintains a p2 repository of binary artifacts. Copy and paste this link into the \"Install New Software\" dialog to install this project's software.</p>\n";
	
	$html .= "<h4>Snapshots (Will be deprecated soon)</h4><p><i>http://download.eclipse.org/rmf/updates/snapshot</i></p>\n";
	$html .= "<h4>Latest Build</h4><p><i>http://download.eclipse.org/rmf/updates/latest</i></p>\n";
	$html .= "<h4>Milestones (Will be published soon)</h4><p><i>http://download.eclipse.org/rmf/updates/milestones</i></p>\n";
	
	$html .= "<h4>Releases (Will be published soon)</h4><p><i>http://download.eclipse.org/rmf/updates/releases</i></p>\n";
	$html .= "<p>Note that the repository link will not necessarily display anything meaningful in your browser.</p>\n";
	$html .= "<h3>Third-Party Distributions</h3>\n";
	$html .= "<a href='http://www.formalmind.com/studio' target='_blank'><img src='http://www.formalmind.com/sites/files/studio-download.png' align='right'></a>\n";
	$html .= "Formal Mind offers a distribution called <a href='http://www.formalmind.com/studio' target='_blank'>formalmind Studio</a>.  This distribution consists of the latest ProR (at times more up to date than the downloads offered here), enhanced with the free Essentials, a suite of productivity enhancements.  Essentials include Rich Text rendering and editing, suspect link management, and more.\n";

	$html .= "<h3>Standalone Snapshots</h3>\n";
	$html .= printArtifacts($folder);

	// # Footer
	$html .= file_get_contents('pages/_footer.html');

	# Generate the web page
	$App->AddExtraHtmlHeader('<script src="/modeling/includes/downloads.js" type="text/javascript"></script>' . "\n"); //ie doesn't understand self closing script tags, and won't even try to render the page if you use one
	$App->generatePage($theme, $Menu, null, $pageAuthor, $pageKeywords, $pageTitle, $html);

?>
