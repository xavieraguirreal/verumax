<?php
/**
 * Proxy a Credencialis - Portal Principal
 * SAJuR - Sociedad Argentina de Justicia Restaurativa
 *
 * Este archivo redirige al motor central de Credencialis
 * con la institución pre-configurada.
 */

// Auto-configuración de institución
$_POST['institutio'] = $_GET['institutio'] = 'sajur';

// Incluir el motor central de Credencialis
require_once dirname(dirname(__DIR__)) . '/credencialis/index.php';
