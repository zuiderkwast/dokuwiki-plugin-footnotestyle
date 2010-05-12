<?php
/**
 * Options for the plugin
 *
 * @author Viktor Söderqvist <viktor@zuiderkwast.se>
 */

$meta['style']       = array('multichoice','_choices' => array('super','square','supersquare','rightparen'));
$meta['stylebottom'] = array('multichoice','_choices' => array('dot', 'square', 'super','supersquare', 'superrightparen'));
$meta['amalgamate']  = array('multichoice','_choices' => array('comma','space','spacecomma','off'));

/*
$meta['regprotect'] = array('onoff');
$meta['forusers']   = array('onoff');
$meta['width']      = array('numeric','_pattern' => '/[0-9]+/');
$meta['height']     = array('numeric','_pattern' => '/[0-9]+/');
*/
