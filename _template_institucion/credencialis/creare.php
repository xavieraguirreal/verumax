<?php
/**
 * Proxy a Credencialis - Generación de Credencial
 * {{NOMBRE_INSTITUCION}}
 *
 * Este archivo redirige al motor central de Credencialis
 * con la institución pre-configurada.
 */

// Auto-configuración de institución
$_POST['institutio'] = $_GET['institutio'] = '{{SLUG}}';

// Incluir el motor central de Credencialis
require_once dirname(dirname(__DIR__)) . '/credencialis/creare.php';
