<?php
require('css_results.php');
require('../css_parser.php');
ini_set('display_errors','On');

$css = new csstidy();

$css_code = file_get_contents('fisubsilver.css');

$css->parse($css_code);

if($css->css === $xhtml_result) {
    echo '<div style="color:green">XHTML OK!</div>';
} else {
    echo '<div style="color:red">XHTML failed!</div>';
}
flush();

$css_code = file_get_contents('base.css');

$css->parse($css_code);

if($css->css === $ala_result) {
    echo '<div style="color:green">ALA OK!</div>';
} else {
    echo '<div style="color:red">ALA failed!</div>';
}
flush();

$css->set_cfg('remove_last_;',true);

if($css->print_code() === $ala_html) {
    echo '<div style="color:green">ALA HTML OK!</div>';
} else {
    echo '<div style="color:red">ALA HTML failed!</div>';
}
flush();

$css->set_cfg('optimise_shorthands',false);
$css->set_cfg('only_safe_optimisations',false);
$css->set_cfg('save_comments',true);
$css->set_cfg('merge_selectors',1);

$css->parse($css_code);

if($css->css === $ala_options_result) {
    echo '<div style="color:green">ALA +options OK!</div>';
} else {
    echo '<div style="color:red">ALA +options failed!</div>';
}
flush();

if($css->comments === $ala_options_comments) {
    echo '<div style="color:green">ALA comments OK!</div>';
} else {
    echo '<div style="color:red">ALA comments failed!</div>';
}
flush();

?>