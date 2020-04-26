<?
//local/php_interface/x/tools/iblock.php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
global $USER;
if (!$USER->isAdmin()) die('сначал авторизуйтесь');

?>
<!DOCTYPE html>
<html>
<head>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <style>
        h2 {
            font-size: medium;
            margin-bottom: 0;
        }
        
        h3 {
            font-size: medium;
            margin: 0;
        }
        
        body {
            padding-top: 20px;
        }
        
        .iblock_panel {
            height: 32px;
            border-bottom: 1px solid #888;
            position: fixed;
            top: 0;
            line-height: 32px;
            background: #aaa;
            width: 100%;
        }
        
        a {
            color: #224;
        }
        
        .out_links {
            color: #5656ca;
        }
        .out_links a {
            color: #5656ca;
        }
        
        .in_links {
            color: #c37a58;
        }
        .in_links a {
            color: #c37a58;
        }
        
        .iblock {
            transition: all 1s ease;
            border-bottom: 1px solid #aaa;
        }
        
        .empty {
            display: none;
            opacity: 0.6;
        }
        
        .props, .raw {
            display: none;
        }
        
        .highlighted {
            background-color: #fa0;
        }
    </style>
</head>
<body>

<header>
    
</header>