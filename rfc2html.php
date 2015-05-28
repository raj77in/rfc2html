<?php
/* rfc2html-php - a converter from plain text RFC to HTML
 * Copyright (C) 2004~2006 Chang Hsiou-Ming
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details. 
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston,
 * MA  02110-1301, USA.
 */

/** 
 * @version $Id: rfc2html.php,v 1.9 2006/02/08 21:44:42 chmate Exp $
 * @author Chang Hsiou-Ming <chmate@gmail.com>
 */

echo '<?xml version="1.0" encoding="iso-8859-1" ?>';
echo "\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">


<?php

define("RFC_SIZE", 1024 * 1024 * 4);
define("PAGE_COLUMNS", 72);
define("BUF_SIZE", 8192);
define("CENTRAL_ERROR", 4);
define("REF_PATTERN", '/RFC\s*(\d{1,4})/i');
define("REF_REPLACE", '<a class="ref" href="rfc2html.php?in=\1">\0</a>');
define("REFED_REPLACE", '<a class="ref" name="REF\1" href="rfc2html.php?in=\1">\0</a>');
# define("REFED_REPLACE", '<a name="REF\1">\0</a>');
define("SEC_NUMBER", '/^(\d+(\.(\d|\w)+)*)(\s|\.)/');
define("SEC_PATTERN", '/((section|sec)\s*(\d+(\.\d+)*))/i');
define("SEC_REPLACE", '<a class="sec" href="#SEC\3">\0</a>');
define("RFCPATH",'../');
$schemes = array('http', 'https', 'ftp');

function rfc2html($pages)
{
	echo "<div class='pages'>\n";
	list($title, $title_lineno) = find_title($pages);

	$rfc_toc = false;
	$rfc_toc_skip = false;
	$toc = array();
	$sec_state = 'normal';
	$sec_idt = 0;
	$sec_last_number = 0;
	$sec_last_number_nidt = 0;
	$nsec = 0;
	
	foreach($pages as $npage => $page) {
		echo "<div class='page'>\n";
		$lines = page2lines($page);

		$pfoot = $lines[count($lines) - 1];
		array_pop($lines);

		// page head
		if($npage == 0) {
			echo "<a name='Page1'></a>\n";
		} else {
			$phead = array();
			while($lines[0] != '') {
				array_push($phead, $lines[0]);
				array_shift($lines);
			}

			$phead = join("<br />", $phead);
			preg_match('/Page\s*(\d+)/i', $pfoot, $match);
			echo "<div class='pagehead'><a name='Page{$match[1]}'>{$phead}</a></div>\n";
		} 
		
		// page content
		echo "<pre>";
		for($i = 0; $i < count($lines); $i++) {
			$lc = $lines[$i];
			$lp = $i > 0 ? $lines[$i - 1] : '';
			$ln = $i < count($lines) - 1 ? $lines[$i + 1] : '';

			$lc_trimed = trim($lc);
			$lc_len = strlen($lc);
			$lc_nidt = count_indent($lc);
			$lc_respace = preg_replace('/\s+/', ' ', $lc_trimed);

			$lc_sec = $lc_respace;
			if(preg_match(SEC_NUMBER, $lc_respace, $match) && $match[4] == '.') {
				$lc_sec = preg_replace(SEC_NUMBER, '$1', $lc_respace);
			}

			if($lc_trimed == '') {
				echo "\n";
				continue;
			}

			$lc_type = 'normal';
			if($lc_trimed == $title) {
				$lc_type = 'rfc_title';
				
			} else if($npage == 0 && $i <= $title_lineno) {
				; // do nothing
				
			} else if(is_array($rfc_toc)) {
				if(count($rfc_toc) > 0) {
					$tmp = strtolower($lc_sec);
					$n = false;
					foreach($rfc_toc as $k => $v) {
						if($tmp === $v[1]) {
							$n = $k;
							break;
						}
					}
					if($n !== false) {
						$lc_type = 'sec_title';
						$sec_state = 'normal';

						if(preg_match('/Reference(s?)/i', $lc_sec)) // no '$'
							$sec_state = 'reference';

						array_push($toc, $rfc_toc[$n][0]);
						//unset($rfc_toc[$n]);
					}
				}	
			} else {
				if(preg_match('/^\s*Table\s+of\s+Content(s?)\s*$/i', $lc_trimed)) {
					$lc_type = 'sec_title';
					$sec_state = 'toc';
					$rfc_toc = array();
					
				} else if(preg_match('/Reference(s?)$/i', $lc_trimed)) {
					$lc_type = 'sec_title';
					$sec_state = 'reference';
					
				} else if($nsec == 0) {
					if(trim($lp) === '' && trim($ln) === '') {
						$lc_type = 'sec_title';
						$sec_state = 'normal';
						$sec_idt = $lc_nidt;
					}
				} else if(trim($lp) === '' && trim($ln) === '') {
					if($lc_nidt == $sec_idt) {
						$lc_type = 'sec_title';
					} else if(preg_match(SEC_NUMBER, $lc_sec, $match)) {
						$last = $sec_last_number;
						$cur = explode('.', $match[1]);
						$len_c = count($cur);
						$len_l = count($sec_last_number);

						for($j = 0; $j < $len_c && $j < $len_l && $last[$j] == $cur[$j]; $j++)
							;

						if($len_c == $len_l) {
							if($j == $len_c - 1 && $last[$j] == $cur[$j] - 1)
								$lc_type = 'sec_title';
						} else if($len_c == $len_l + 1) {
							if($cur[$len_c - 1] == 1)
								$lc_type = 'sec_title';
						} else if($len_c < $len_l) {
							if($j == $len_c - 1 && $last[$j] == $cur[$j] - 1 &&
									$sec_last_number_nidt > $lc_nidt)
								$lc_type = 'sec_title';
						}
					}

					if($lc_type == 'sec_title')
						$sec_state = 'normal';
				}
			}


			if($lc_type == 'rfc_title') {
				echo "</pre><h1>{$lc_trimed}</h1><pre>\n";
				
			} else if($lc_type == 'sec_title') {
				$nsec++;
				if(preg_match(SEC_NUMBER, $lc_sec, $match)) {
					echo "<a name='SEC{$match[1]}'></a>";
					$sec_last_number = explode('.', $match[1]);
					$sec_last_number_nidt = $lc_nidt;
				} else {
					$t = bin2hex($lc_sec);
					echo "<a name='SEC{$t}'></a>";
				}	
				
				$tag = 'h2';
				if(preg_match('/^\d+\.\d+/', $lc_sec))
					$tag = 'h3';
				
				if(!is_array($rfc_toc))
					array_push($toc, $lc_sec);
				echo "</pre><div style='padding-left: {$lc_nidt}ex'><$tag>$lc_trimed</$tag></div><pre>";
					
			} else {
				if($sec_state == 'normal') {
					$lc = preg_replace(REF_PATTERN, REF_REPLACE, $lc);
					$lc = preg_replace(SEC_PATTERN, SEC_REPLACE, $lc);
					
				} else if($sec_state == 'reference') {
					$lc = preg_replace(REF_PATTERN, REFED_REPLACE, $lc);
					
				} else if($sec_state == 'toc') {
					if($rfc_toc_skip)
						$rfc_toc_skip = false;
					else if(preg_match('/^(\w)/i', $lc_sec)) {
						$t = trim(preg_replace('/(\.+\s*\d+)$/', '', $lc));
						if($t == $lc_trimed) {
							$t .= ' ' . trim($ln);
							$t = trim(preg_replace('/(\.+\s*\d+)$/', '', $t));
							$rfc_toc_skip = true;
						}
					
						$t = preg_replace('/\s+/', ' ', $t);
						if(preg_match(SEC_NUMBER, $t, $match) && $match[4] == '.')
							$t = preg_replace(SEC_NUMBER, '$1', $t);
						array_push($rfc_toc, array($t, strtolower($t)));
					}
				}
				
				echo "$lc\n";
			}
		}
		echo "</pre>";
		
		// page foot
		echo "<div class='pagefoot'>$pfoot</div>\n";
		echo "</div><!-- page -->\n";
	}

	//echo '<pre>'; var_dump($rfc_toc); echo '</pre>';	
	$toc = build_toc($toc);

	echo "</div><!-- pages -->\n";

	return $toc;
}

function build_toc($old_toc)
{
	$toc = array();

	for($i = 0; $i < count($old_toc); $i++) {
		$c = $old_toc[$i];
		
		if(preg_match(SEC_NUMBER, trim($c), $match)) {
			$t = explode('.', $match[1]);
			$p = &$toc;
			while(count($t) > 0) {
				if(count($p) == 0 || $p[count($p) - 1][1] != $t[0]) {
					$d = array($c, $t[0], array(), 'SEC' . $match[1]);
					array_push($p, $d);
				} else {
					$p = &$p[count($p) - 1][2];
				}
				array_shift($t);
			}
		} else {
			$d = array($c, NULL, array(), 'SEC' . bin2hex($c));
			array_push($toc, $d);
		}
	}

	return $toc;
}

function print_list($list, $parent, $depth)
{
	if(count($list) == 0)
		return;

	if($depth == 0)
		echo "<ul id='u$parent' style='display: block;'>\n";
	else
		echo "<ul class='ul_toc' id='u$parent'>\n";
		
	foreach($list as $i) {
		$t = count($i[2]) == 0 ? '-' : '+';
		echo "<li><span class='expand' id='s{$i[3]}'>$t</span>" .
			"<a href='#{$i[3]}'>{$i[0]}</a>\n";

		if(count($i[2]) > 0) {
			print_list($i[2], $i[3], $depth + 1);
		}
			echo "</li>\n";
	}
	echo "</ul>\n";
}

function print_toc($toc)
{
	echo "<div class='toc'>\n";
	echo "<div onclick='expand_all();' id='expand_all'>Expand All</div>\n";
	print_list($toc, 'top_toc', 0);
	echo "</div><!-- toc -->\n";
}

function print_pages($num_page)
{
	echo "<ul id='pages'>\n";
	for($i=0; $i<$num_page; $i++) {
		echo "<li><a href='#Page$i'>P$i</a></li>";
	}
	echo "</ul>\n";
	echo "<div style='clear:both'>\n";
	echo "</div>\n";
}

function count_indent($line)
{
	$n = 0;
	for($i=0; $i<strlen($line); $i++) {
		$c = $line{$i};
		if($c == " ")
			$n++;
		else if($c == "\t")
			$n += 8;
		else
			break;
	}

	return $n;
}

function find_title($pages)
{
	foreach($pages as $p) {
		$lines = page2lines($p);
		foreach($lines as $i => $v) {
			$vt = trim($v);
			if($vt == '')
				continue;

			if($v{0} == "\t")
				return array($vt, $i);

			$in = count_indent($v);
			if($in == 0)
				continue;

			if(abs($in - (PAGE_COLUMNS - strlen($v))) <= CENTRAL_ERROR)
				return array($vt, $i);
		}
	}

	return false;
}

function page2lines($page)
{
	$tmp = explode("\n", $page);
	$n = 0;
	$lines = array();
	foreach($tmp as $v) {
		$v = htmlspecialchars($v);
		array_push($lines, rtrim($v));
	}

	while(count($lines) > 1 && $lines[0] == '')
		array_shift($lines);

	while(count($lines) > 1 && $lines[count($lines) - 1] == '')
		array_pop($lines);
	
	return $lines;
}


?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script type="text/javascript">
<!--
function add_event(obj, e_type, func)
{
	if(obj.addEventListener){
		obj.addEventListener(e_type, func, false);
		return true;
	} else if(obj.attachEvent){
		var r = obj.attachEvent("on"+e_type, func);
		return r;
	} else {
		return false;
	}
}

function load()
{
	var elms = document.getElementsByTagName('span');
	for(var i=0; i<elms.length; i++) {
		if(elms.item(i).className != 'expand')
			continue;
		//document.write(i + '<br />');
		//elms.item(i).addEventListener("click", expand, false);
		add_event(elms.item(i), "click", expand);
	}

	init_deck();
}

function expand_all()
{
	var ea = document.getElementById('expand_all');
	var elms = document.getElementsByTagName('ul');

	var d = 'block';
	if(ea.innerHTML == 'Expand All') {
		ea.innerHTML = 'Collapse All';
	} else {
		ea.innerHTML = 'Expand All';
		d = 'none';
	}

	for(var i=0; i<elms.length; i++) {
		if(elms.item(i).className != 'ul_toc')
			continue;
		elms.item(i).style.display = d;
	}
	
}

function expand(e)
{
	var elm;
	if(window.event && window.event.srcElement){
		elm = window.event.srcElement;
	} else if(e && e.target){
		elm = e.target;
	}

	if(!elm)
		return;

	var id = 'u' + elm.id.substr(1);
	var ue = document.getElementById(id);
	if(ue) {
		if(ue.style.display == 'block')
			ue.style.display = 'none';
		else
			ue.style.display = 'block';
	}
	
}

function init_deck()
{
	var navlist = document.getElementById('navlist');
	if(!navlist)
		return;

	var tabs = navlist.childNodes;
	for(var i=0; i<tabs.length; i++) {
		if(tabs[i].nodeType != 1 || tabs[i].tagName.toLowerCase() != 'li')
			continue;
		var e = tabs[i].firstChild;
		add_event(e, "click", change);
	}
}

function change(e)
{
	var elm;
	if(window.event && window.event.srcElement){
		elm = window.event.srcElement;
	} else if(e && e.target){
		elm = e.target;
	}

	if(!elm)
		return;

	id = elm.getAttribute('title');
	if(!id) {
		id = elm.innerHTML;
	}

	id = 'deck_' + id;


	var navlist = document.getElementById('navlist');
	if(!navlist)
		return;
	var tabs = navlist.childNodes;
	for(var i=0; i<tabs.length; i++) {
		if(tabs[i].nodeType != 1 || tabs[i].tagName.toLowerCase() != 'li')
			continue;
		tabs[i].className = '';
	}
	elm.parentNode.className = 'select';
	
	var decks = document.getElementById('decks').childNodes;
	for(var i=0; i<decks.length; i++) {
		if(decks[i].nodeType != 1 || decks[i].tagName.toLowerCase() != 'div')
			continue;
		if(decks[i].id == id)
			decks[i].style.display = 'block';
		else
			decks[i].style.display = 'none';
	}
}
-->
</script>
<link type="text/css" href="/css/rwstyle.css" rel="stylesheet" />
<link type="text/css" href="rfc2html.css" rel="stylesheet" />
<?php @include 'rfc2html_style.php'; ?>	

<?php
if($_GET['in']) {
	$from = '';
	
	$in = $_GET['in'];
	$rfcno = '';
	if(ctype_digit($in)) {
		$rfcno = $in;
		if(file_exists(RFCPATH."/rfc$in.txt")) {
			$filename = RFCPATH."/rfc$in.txt";
			$from = 'local';
		} else {
			$filename = "http://www.ietf.org/rfc/rfc$in.txt";
			$from = 'ietf';
		}
	} else {
		$a = parse_url($in);
		if(in_array($a['scheme'], $schemes)) {
			$filename = $in;
			$from = $a['scheme'];
		}
	}

	if($filename) {
		$fp = @fopen($filename, "r");
		if(!$fp) {
			
		} else {
			$text = '';
			while(!feof($fp)) {
				$text .= fread($fp, BUF_SIZE);
				if(strlen($text) > RFC_SIZE) {
					break;
				}
			}

			fclose($fp);
			
			if($from == 'ietf') { // cache
				$fw = @fopen(basename($filename), 'w');
				if($fw)
					fwrite($fw, $text);
			}
		}
	}
}
?>

<title>
<?php
	if($text) {
		$pages = explode("\x0C\n", $text);
		list($title, $lineno) = find_title($pages);

		if($rfcno) {
			echo "RFC$rfcno ";
		}
		echo "$title - rfc2html";
	} else {
		echo "rfc2html";
	}
?>
</title>

</head>

<body onload="load();" >

<div><a name="top"></a></div>

<div class="head">
	<?php @include 'rfc2html_head.php'; ?>	
</div>

<!-- Top Toolbar commented out by Amit Agarwal
<div class="toolbar">
	<form method="get" action="rfc2html.php">
		<div>
			<label for="in">Input a RFC number (retrieve from IETF) or an URL:</label>
			<input type="text" name="in" id="in" size="30" />
			<input type="submit" value="Go!" />
		</div>
	</form>
</div>
-->

<?php
	if($text) {
		echo "<div class='rfc'>\n";
		$toc = rfc2html($pages);
		?>
		<div id='sidebar'>
			<div id="navbar">
				<ul id="navlist">
					<li class='select'><span>TOC</span></li>
					<li><span>Pages</span></li>
				</ul>
			</div>
		
			<div id='decks'>
				<div class='desk select' id='deck_TOC'>
					<?php print_toc($toc); ?>
				</div><!-- deck -->
				<div class='desk' id='deck_Pages'>
					<?php print_pages(count($pages)); ?>
				</div><!-- deck -->
			</div><!-- decks -->
		</div><!-- sidebar -->
		<?php
		echo "</div><!-- rfc -->\n";
	}
?>
<a class='gotop' href='#top'>top</a>

</body>
</html>

