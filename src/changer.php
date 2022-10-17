<?php

/**
 * AbraFlex Order status changer.
 *
 * @author     Vítězslav Dvořák <vitezslav.dvorak@spojenet.cz>
 * @copyright  2022 Spoje.Net
 */
define('APP_NAME', 'AbraFlexiOrderChanger');
require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists('../.env')) {
    \Ease\Shared::singleton()->loadConfig('../.env', true);
}

foreach (['ABRAFLEXI_URL', 'ABRAFLEXI_LOGIN', 'ABRAFLEXI_PASSWORD', 'ABRAFLEXI_COMPANY', 'EASE_LOGGER'] as $cfgKey) {
    if (empty(\Ease\Functions::cfg($cfgKey))) {
        echo 'Requied configuration ' . $cfgKey . ' is not set.';
        exit(1);
    }
}

/**
 * Filter value by keyword
 * 
 * @param string $noteRaw
 * 
 * @return string
 */
function stateExtract($noteRaw, $keyword) {
    $state = null;
    if (strstr($noteRaw, ':')) {
        foreach (preg_split('/\n/', $noteRaw) as $noteLine) {
            if (stristr($noteLine, $keyword)) {
                $state = trim(preg_split('/: /', $noteLine, 2)[1], " \t\n\r\0\x0B'\"");
            }
        }
    }
    return $state;
}

/**
 * 
 * @param string $needle
 * @param string $haystack
 * 
 * @return boolean
 */
function valueFound(string $needle, string $haystack) {
    $found = false;
    foreach (explode(',', $haystack) as $candidat) {
        if ($candidat == $needle) {
            $found = true;
        }
    }
    return $found;
}

$keyword = \Ease\Functions::cfg('ORDER_NOTE_KEYWORD', 'Note:');

if ($argc > 1) {
    $docId = $argv[1];
} else {
    $docId = \Ease\Functions::cfg('DOCUMENTID');
}

try {
    $orderer = new \AbraFlexi\ObjednavkaPrijata($docId);

//  AbraFlexi States Availble:
//
//    Připraveno (stavDoklObch.pripraveno)
//    Schváleno (stavDoklObch.schvaleno)
//    Částečně na cestě (stavDoklObch.castecneNaCeste)
//    Na cestě (stavDoklObch.naCeste)
//    Částečně vydáno/přijato (stavDoklObch.castVydano)
//    Vydáno/přijato (stavDoklObch.vydano)
//    Částečně hotovo (stavDoklObch.castHotovo)

    $state = stateExtract($orderer->getDataValue('poznam'), $keyword);

#    Nespecifikováno 
    if (valueFound($state, \Ease\Functions::cfg('STAV_DOKL_OBCH_NESPEC'))) {
        $stavUzivK = 'stavDoklObch.nespec';
    } else
#    Připraveno
    if (valueFound($state, \Ease\Functions::cfg('STAV_DOKL_OBCH_PRIPRAVENO'))) {
        $stavUzivK = 'stavDoklObch.pripraveno';
    } else
#    Schváleno
    if (valueFound($state, \Ease\Functions::cfg('STAV_DOKL_OBCH_SCHVALENO'))) {
        $stavUzivK = 'stavDoklObch.schvaleno';
    } else
#    Částečně na cestě
    if (valueFound($state, \Ease\Functions::cfg('STAV_DOKL_OBCH_CASTECNENACESTE'))) {
        $stavUzivK = 'stavDoklObch.castecneNaCeste';
    } else
#    Na cestě
    if (valueFound($state, \Ease\Functions::cfg('STAV_DOKL_OBCH_NACESTE'))) {
        $stavUzivK = 'stavDoklObch.naCeste';
    } else
#    Částečně vydáno/přijato
    if (valueFound($state, \Ease\Functions::cfg('STAV_DOKL_OBCH_CASTVYDANO'))) {
        $stavUzivK = 'stavDoklObch.castVydano';
    } else
#    Vydáno/přijato 
    if (valueFound($state, \Ease\Functions::cfg('STAV_DOKL_OBCH_VYDANO'))) {
        $stavUzivK = 'stavDoklObch.vydano';
    } else
#    Částečně hotovo 
    if (valueFound($state, \Ease\Functions::cfg('STAV_DOKL_OBCH_CASTHOTOVO'))) {
        $stavUzivK = 'stavDoklObch.castHotovo';
    } else
#    Hotovo
    if (valueFound($state, \Ease\Functions::cfg('STAV_DOKL_OBCH_HOTOVO'))) {
        $stavUzivK = 'stavDoklObch.hotovo';
    } else
#    Storno 
    if (valueFound($state, \Ease\Functions::cfg('STAV_DOKL_OBCH_STORNO'))) {
        $stavUzivK = 'stavDoklObch.storno';
    } else {
        $stavUzivK = false;
        $orderer->addStatusMessage('ORDER_NOTE_KEYWORD "' . $keyword . '" not found in order note.', 'warning');
    }




    if ($orderer->getRecordID()) {

        if ($stavUzivK) {
            $orderer->stripBody();
            $orderer->setDataValue('stavUzivK', $stavUzivK);
            $result = $orderer->sync();
            $orderer->addStatusMessage('Change to ' . $stavUzivK . ' for "' . $state . '" state', $result ? 'success' : 'error' );
        } else {
            $orderer->addStatusMessage(_('Order without known state'), 'warning');
        }
    } else {
        $orderer->addStatusMessage(_('The DOCUMENTID is not specified. Aborting'), 'error');
    }
} catch (AbraFlexi\Exception $exc) {
    echo $exc->getMessage();
    exit(1);
}
