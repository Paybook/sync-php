<?php

require __DIR__.'/vendor/autoload.php';

$INDENT = '   ';
$API_KEY = null;
$USERS = null;
$USER = null;
$SESSION = null;
$RFC = null;
$SAT_PASSWORD = null;
$SAT_SITE = 'CIEC';
$CREDENTIALS = null;
$TRANSACTIONS = null;
$ATTACHMENTS = null;
$BAR_LEN = 75;
$DOWNLOAD_LIMIT = 10;

function folder_exist($folder)
{
    $path = realpath($folder);

    return ($path !== false and is_dir($path)) ? $path : false;
}//End of folder_exist

function waiting_message($time, $message = '', $indent = 0)
{
    global $INDENT;
    $indents = '';
    for ($i = 0; $i < $indent; ++$i) {
        $indents = $indents.$INDENT;
    }//End of for
    for ($i = 0; $i <= 4 * $time; ++$i) {
        if ($i % 2) {
            echo $indents.$message."   \\\r";
        } else {
            echo $indents.$message."   /\r";
        }//End of if
        usleep(250000);
    }//End of for
    echo $indents.$message.'   \\0/';
    _print('');
}//End of waiting_message

function _print($message, $indent = 0)
{
    global $INDENT;
    $indents = '';
    for ($i = 0; $i < $indent; ++$i) {
        $indents = $indents.$INDENT;
    }//End of for
    print_r($indents.$message.PHP_EOL);
}//End of _print

function print_step_separator($step_number)
{
    _print('');
    _print('---------------------------------------------------------------------------------------------------');
    _print('');
    _print(strtoupper('Paso ').strval($step_number), 1);
    _print('');
}//End of print_step_separator

function print_input_error($input_error)
{
    global $INDENT;
    _print('');
    _print('¡Ups!   '.$input_error->input_message, 3);
}//End of print_input_error

function get_status_str($status)
{
    $status_str = '';
    foreach ($status as $index => $each_status) {
        $code = $each_status['code'];
        $status_str = $status_str.$code.', ';
    }//End of foreach
    $status_str = substr($status_str, 0, strlen($status_str) - 2);

    return str_pad($status_str, 23);
}//End of get_status_str

function get_input_value()
{
    _print('');
    global $INDENT;
    if (PHP_OS == 'WINNT') {
      $input_value = stream_get_line(STDIN, 1024, PHP_EOL);
    } else {
      $input_value = readline($INDENT.$INDENT.$INDENT.'Respuesta: ');
    }
    
    _print('');

    return $input_value;
}//End of get_input_value

function create_script($api_key, $id_user, $id_credential)
{
    $source = 'template.php';
    $target = 'example.php';

    $sh = fopen($source, 'r');
    $th = fopen($target, 'w');
    while (!feof($sh)) {
        $line = fgets($sh);
        if (strpos($line, '$api_key = ') !== false) {
            $line = '$api_key = "'.$api_key.'";'.PHP_EOL;
        } elseif (strpos($line, '$id_user = ') !== false) {
            $line = '$id_user = "'.$id_user.'";'.PHP_EOL;
        } elseif (strpos($line, '$id_credential = ') !== false) {
            $line = '$id_credential = "'.$id_credential.'";'.PHP_EOL;
        }//End of if
        fwrite($th, $line);
    }//End of while

    fclose($sh);
    fclose($th);

    // unlink($source);
    // rename($target, $source);
}//End of create_script

class InputError extends Exception
{
    public function __construct($step_number, $message, $origin = null)
    {
        $this->step_number = $step_number;
        $this->input_message = $message;
        $this->origin = $origin;
    }//End of __construct
}//End of InputError

function go_to_step($step_number, $second_try = false)
{
    try {
        global $INDENT;
        global $API_KEY;
        global $USERS;
        global $USER;
        global $SESSION;
        global $RFC;
        global $PASSWORD;
        global $CREDENTIALS;
        global $TRANSACTIONS;
        global $ATTACHMENTS;
        global $BAR_LEN;
        global $DOWNLOAD_LIMIT;

        /*
        GET API KEY
        */
        if ($step_number == 1) {
            $api_key_length = 32;
            if (!$second_try) {
                print_step_separator($step_number);
                _print('Por favor, introduce tu API key de SYNC.', 2);
            }//End of if
            $api_key = get_input_value();
            $API_KEY = $api_key;
            if (strlen($api_key) != $api_key_length) {
                throw new InputError($step_number, 'SYNC API KEY inválida. Por favor introduce un API KEY válida.');
            }//End of if
            try {
                paybook\Paybook::init($api_key);
                $USERS = paybook\User::get();
                waiting_message(3, 'Inicializando Paybook Sync SDK y trayendo usarios ...', 2);
            } catch (paybook\Error $e) {
                if ($e->get_code() == 401) {
                    throw new InputError($step_number, 'SYNC API KEY inválida. Por favor introduce un API KEY válida.');
                } else {
                    _print($e->get_code().' '.$e->get_message(), 1);

                    return;
                }//End of if
            }//End of try
            go_to_step(2);

            return;
        /*
        SELECT A USER:
        */
        } elseif ($step_number == 2) {
            $create_user_option = 1;
            $total_users = count($USERS);
            $user_by_option_value = [];
            foreach ($USERS as $index => $user) {
                $option_value = $index + 2;
                $user_by_option_value[$option_value] = $user;
            }//End of foreach 
            if (!$second_try) {
                print_step_separator($step_number);
                $create_user_message = 'Crear usuario';
                _print('Por favor, selecciona el usuario que utilizarás para descargar las facturas.', 2);
                _print('');
                _print('['.strval($create_user_option).'] '.strtoupper($create_user_message), 3);
                foreach ($USERS as $index => $user) {
                    $option_value = $index + 2;
                    _print('['.strval($option_value).'] '.$user->name, 3);
                }//End of foreach 
            }//End of if
            $user_option = get_input_value();
            $user_option = intval($user_option);
            if ($user_option < $create_user_option || $user_option > ($total_users + 1)) {
                throw new InputError($step_number, 'Tu respuesta debe ser un número en el intervalo: ['.strval($create_user_option).','.strval($total_users + 1).']');
            }//End of if
            if ($user_option == $create_user_option) {
                // 2.1
                go_to_step(21);
            } else {
                $USER = $user_by_option_value[$user_option];
                go_to_step(3);
            }//End of if
            return;
        /*
        CREATE A USER 2.1:
        */
        } elseif ($step_number == 21) {
            if (!$second_try) {
                _print('');
                _print('Por favor, introduce el nombre del usuario nuevo.', 2);
            }//End of if
            $username = get_input_value();
            if (!$username) {
                throw new InputError($step_number, 'Introduce un nombre válido (no vacío)');
            }//End of if
            waiting_message(3, 'Creando usuario "'.$username.'" ...', 2);
            $USER = new paybook\User($username);
            _print('Identificador de usuario:    '.$USER->id_user, 3);
            go_to_step(3);

            return;
        /*
        CREATE SESSION:
        */
        } elseif ($step_number == 3) {
            print_step_separator($step_number);
            _print('Usuario seleccionado: '.$USER->name, 2);
            waiting_message(3, 'Creando sesión ...', 2);
            $SESSION = new paybook\Session($USER);
            _print('Token de sesión obtenido:    '.$SESSION->token, 2);
            go_to_step(4);

            return;
        /*
        GET RFC 4:
        */
        } elseif ($step_number == 4) {
            if (!$second_try) {
                print_step_separator($step_number);
                _print('');
                _print('Por favor, introduce el RFC.', 2);
            }//End of if
            $RFC = get_input_value();
            $RFC = strtoupper($RFC);
            if (!$RFC) {
                throw new InputError($step_number, 'Introduce un RFC válido');
            }//End of if
            if (!preg_match('/^([A-ZÑ\x26]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1]))((-)?([A-Z\d]{3}))?$/', $RFC)) {
                throw new InputError($step_number, 'Introduce un RFC válido');
            }//End of if
            go_to_step(41);

            return;
        /*
        GET PASSWORD 4.1:
        */
        } elseif ($step_number == 41) {
            $sat_password_length = 8;
            if (!$second_try) {
                _print('');
                _print('Por favor, introduce la contraseña del SAT (antes clave CIEC) del RFC '.$RFC.'.', 2);
            }//End of if
            $PASSWORD = get_input_value();
            if (strlen($PASSWORD) != $sat_password_length) {
                throw new InputError($step_number, 'Tu contraseña del SAT debe contener '.strval($sat_password_length).' carácteres');
            }//End of if
            go_to_step(5);

            return;
        /*
        CREATE CREDENTIALS:
        */
        } elseif ($step_number == 5) {
            global $SAT_SITE;
            print_step_separator($step_number);
            _print('RFC introducido:                     '.$RFC, 2);
            _print('Contraseña del SAT introducida:      '.$PASSWORD[0].'******'.$PASSWORD[7], 2);
            waiting_message(2, 'Obteniendo identificador del sitio del SAT ...', 2);
            $sites = paybook\Catalogues::get_sites($SESSION);
            $site = null;
            foreach ($sites as $index => $site_option) {
                if ($site_option->name == $SAT_SITE) {
                    $site = $site_option;
                }//End of if
            }//End of foreach
            if (is_null($site)) {
                throw new InputError($step_number, 'Error al obtener sitio del SAT');
            }//End of if
            _print('Identificador obtenido', 2);
            _print(' - Sitio: '.$site->name, 3);
            _print(' - Identificador del sitio (id_site): '.$site->id_site, 3);
            $credentials_params = [
                'rfc' => $RFC,
                'password' => $PASSWORD,
            ];//End of credentials_params
            _print('');
            waiting_message(2, 'Creando credenciales para institución ... ', 2);
            $CREDENTIALS = new paybook\Credentials($SESSION, null, $site->id_site, $credentials_params);
            _print('Credenciales de sincronización creadas exitósamente', 2);
            _print(' - Identificador de las credenciales (id_credential): '.$CREDENTIALS->id_credential, 3);
            go_to_step(6);

            return;
        /*
        VALIDATE CREDENTIALS:
        */
        } elseif ($step_number == 6) {
            print_step_separator($step_number);
            _print('Validando credenciales del SAT: '.$RFC.' y '.$PASSWORD[0].'******'.$PASSWORD[7], 2);
            _print('');
            $credentials_validated = false;
            while (!$credentials_validated) {
                $status = $CREDENTIALS->get_status($SESSION);
                $status_str = get_status_str($status);
                for ($i = 0; $i < 4; ++$i) {
                    if ($i % 2) {
                        echo $INDENT.$INDENT.$INDENT.$status_str."/\r";
                    } else {
                        echo $INDENT.$INDENT.$INDENT.$status_str."\\\r";
                    }//End of if
                    usleep(250000);
                }//End of if
                foreach ($status as $index => $each_status) {
                    $code = $each_status['code'];
                    if ($code == 102) {
                        $credentials_validated = true;
                    } elseif ($code >= 400 && $code <= 405) {
                        echo $INDENT.$INDENT.$INDENT.$status_str.':(';
                        if ($code == 401) {
                            throw new InputError(4, 'Las credenciales '.$RFC.' y '.$PASSWORD[0].'******'.$PASSWORD[7].' son inválidas', $step_number);
                        } else {
                            _print('Error al realizar la sincronización');
                            exit();
                        }//End of if
                    }//End of if
                }//End of foreach
            }//End of while
            echo $INDENT.$INDENT.$INDENT.$status_str.'\\0/';
            _print('');
            go_to_step(7);

            return;
        /*
        WAIT FOR SYNCHRONISATION:
        */
        } elseif ($step_number == 7) {
            print_step_separator($step_number);
            _print('Sincronizando sitio del SAT ... ', 2);
            _print('');
            $sync_completed = false;
            while (!$sync_completed) {
                $status = $CREDENTIALS->get_status($SESSION);

                $status_str = get_status_str($status);
                for ($i = 0; $i < 4; ++$i) {
                    if ($i % 2) {
                        echo $INDENT.$INDENT.$INDENT.$status_str."/\r";
                    } else {
                        echo $INDENT.$INDENT.$INDENT.$status_str."\\\r";
                    }//End of if
                    usleep(250000);
                }//End of if

                foreach ($status as $index => $each_status) {
                    $code = $each_status['code'];
                    if ($code >= 200 && $code <= 205) {
                        if ($code == 202) {
                            echo $INDENT.$INDENT.$INDENT.$status_str.'\\0/';
                            _print('La sincronización fue exitosa pero las credenciales '.$RFC.' y '.$PASSWORD[0].'******'.$PASSWORD[7].' no tienen facturas en el SAT', 2);
                            exit();
                        }//End of if
                        $sync_completed = true;
                    } elseif ($code >= 400 && $code <= 405) {
                        echo $INDENT.$INDENT.$INDENT.$status_str.':(';
                        _print('Error al realizar la sincronización');
                        exit();
                    }//End of if
                }//End of foreach
            }//End of while 
            echo $INDENT.$INDENT.$INDENT.$status_str.'\\0/';
            _print('');
            _print('');
            _print('Sincronización exitosa', 2);
            go_to_step(8);

            return;
        /*
        GET INVOICES:
        */
        } elseif ($step_number == 8) {
            print_step_separator($step_number);
            $options = [
                'id_credential' => $CREDENTIALS->id_credential,
                'limit' => $DOWNLOAD_LIMIT,
            ];//End of $options
            _print('');
            waiting_message(2, 'Obteniendo facturas ... ', 2);
            // $TRANSACTIONS = paybook\Transaction::get($SESSION, null, $options);
            // _print('Facturas sincronizadas en total: '.strval(count($TRANSACTIONS)), 2);
            $ATTACHMENTS = paybook\Attachment::get($SESSION, null, null, null, $options);
            _print(' ');
            // waiting_message(2, 'Obteniendo archivos adjuntos sincronizados ... ', 2);
            _print('Archivos de facturas sincronizados en total: '.strval(count($ATTACHMENTS)), 2);
            go_to_step(9);

            return;
        /*
        DOWNLOAD INVOICES:
        */
        } elseif ($step_number == 9) {
            print_step_separator($step_number);
            $total = count($ATTACHMENTS);
            if ($total > 0) {
                if ($DOWNLOAD_LIMIT) {
                    $total = $DOWNLOAD_LIMIT;
                }//End of if
                _print('Descargando facturas: ', 2);
                _print('');
                $invoice_counter = 0;
                foreach ($ATTACHMENTS as $key => $attachment) {
                    $invoice_counter = $invoice_counter + 1;
                    // begin bar
                    $rate = $invoice_counter / $total;
                    $percentage = $rate * 100;
                    $percentage_str = sprintf('%0.2f', $percentage).'%';
                    $bar_len = intval($rate * $BAR_LEN);
                    $bar = str_pad('', $bar_len, '=', STR_PAD_LEFT);
                    $bar = $INDENT.$INDENT.$INDENT.$percentage_str.' '.$bar."\r";
                    echo $bar;
                    // end bar                    
                    $url = $attachment->url;
                    $id_attachment = substr($url, 1, strlen($url));
                    $attachment_content = paybook\Attachment::get($SESSION, null, $id_attachment);
                    if (!folder_exist('downloads')) {
                        mkdir('downloads');
                    }//End of if
                    if (!folder_exist('downloads/wizard')) {
                        mkdir('downloads/wizard');
                    }//End of if
                    $xml_file = fopen('downloads/wizard/'.$attachment->file, 'w') or die('No se pudo guardar archivo '.$attachment->file);
                    fwrite($xml_file, $attachment_content);
                    fclose($xml_file);
                    if ($DOWNLOAD_LIMIT && $invoice_counter == $DOWNLOAD_LIMIT) {
                        break;
                    }//End of if
                }//End of if
                $bar = str_pad('', $BAR_LEN, '=', STR_PAD_LEFT);
                echo $INDENT.$INDENT.$INDENT.'100.00% '.$bar.' \\0/'.PHP_EOL;
                _print('');
                go_to_step(10);
            } else {
                _print('La sincronización fue exitosa pero las credenciales '.$RFC.' y '.$PASSWORD[0].'******'.$PASSWORD[7].' no tienen archivos adjuntos para descargar', 2);
                exit();
            }//End of if
        /*
        END:
        */
        } elseif ($step_number == 10) {
            print_step_separator($step_number);
            create_script($API_KEY, $USER->id_user, $CREDENTIALS->id_credential);
            _print('Las facturas fueron descargadas en el directorio:', 2);
            _print('');
            _print(__DIR__.'/downloads/wizard', 3);
            _print('');
            _print('En este mismo directorio se te ha creado un script de ejemplo personalizado para descargas de facturas: ', 2);
            _print('');
            _print('example.php', 3);
            _print('');
            _print('Para correrlo ejecuta: ', 2);
            _print('');
            _print('php example.php', 3);
            _print('');
            _print('GRACIAS POR USAR PAYBOOK SYNC :)', 2);
            _print('');
            _print('');
        }//End of if
    } catch (InputError $input_error) {
        print_input_error($input_error);
        if ($input_error->step_number == 4 && $input_error->origin == 6) {
            go_to_step($input_error->step_number);// Ask for creds again
        } else {
            go_to_step($input_error->step_number, true);
        }//End of if
    }//End of try
}//End of _print

go_to_step(1);
