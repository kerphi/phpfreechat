<?php

require_once "../src/phpchatconfig.class.php";
$chatconfig = new phpChatConfig( array("title" => "A chat with one script for client and on script for server",
                                       "pseudo" => "guest",
                                       "data_file"  => "chat_data/chat_client_serveur.data",
                                       "index_file" => "chat_data/chat_client_serveur.index",
                                       "server_file" => "demo3_server.php" ) );

?>