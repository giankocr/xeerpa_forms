<?php
/**
 * Configuración de zona horaria para el plugin XPSocial Login
 * Centraliza la gestión de fechas y horas según la zona horaria de Costa Rica
 */

if (!defined('ABSPATH')) {
    exit;
}

class Xpsocial_Timezone
{
    /**
     * Zona horaria por defecto del plugin
     */
    const DEFAULT_TIMEZONE = 'America/Costa_Rica';
    
    /**
     * Obtener fecha y hora actual en zona horaria de Costa Rica
     * 
     * @param string $format Formato de fecha (por defecto: 'd/m/Y H:i:s')
     * @return string Fecha formateada en zona horaria de Costa Rica
     */
    public static function get_costa_rica_datetime($format = 'd/m/Y H:i:s')
    {
        try {
            $timezone = new DateTimeZone(self::DEFAULT_TIMEZONE);
            $datetime = new DateTime('now', $timezone);
            return $datetime->format($format);
        } catch (Exception $e) {
            // Fallback a la zona horaria del servidor si hay error
            error_log('XPSocial Timezone Error: ' . $e->getMessage());
            return date($format);
        }
    }
    
    /**
     * Obtener timestamp actual en zona horaria de Costa Rica
     * 
     * @return int Timestamp Unix
     */
    public static function get_costa_rica_timestamp()
    {
        try {
            $timezone = new DateTimeZone(self::DEFAULT_TIMEZONE);
            $datetime = new DateTime('now', $timezone);
            return $datetime->getTimestamp();
        } catch (Exception $e) {
            error_log('XPSocial Timezone Error: ' . $e->getMessage());
            return time();
        }
    }
    
    /**
     * Convertir fecha de una zona horaria a Costa Rica
     * 
     * @param string $date Fecha en formato válido
     * @param string $from_timezone Zona horaria origen
     * @param string $format Formato de salida (por defecto: 'd/m/Y H:i:s')
     * @return string Fecha convertida a zona horaria de Costa Rica
     */
    public static function convert_to_costa_rica($date, $from_timezone = 'UTC', $format = 'd/m/Y H:i:s')
    {
        try {
            $from_tz = new DateTimeZone($from_timezone);
            $to_tz = new DateTimeZone(self::DEFAULT_TIMEZONE);
            
            $datetime = new DateTime($date, $from_tz);
            $datetime->setTimezone($to_tz);
            
            return $datetime->format($format);
        } catch (Exception $e) {
            error_log('XPSocial Timezone Conversion Error: ' . $e->getMessage());
            return $date;
        }
    }
    
    /**
     * Obtener información de la zona horaria actual
     * 
     * @return array Información de la zona horaria
     */
    public static function get_timezone_info()
    {
        try {
            $timezone = new DateTimeZone(self::DEFAULT_TIMEZONE);
            $datetime = new DateTime('now', $timezone);
            
            return array(
                'timezone' => self::DEFAULT_TIMEZONE,
                'offset' => $timezone->getOffset($datetime),
                'offset_formatted' => $datetime->format('P'),
                'current_time' => $datetime->format('d/m/Y H:i:s'),
                'current_timestamp' => $datetime->getTimestamp()
            );
        } catch (Exception $e) {
            error_log('XPSocial Timezone Info Error: ' . $e->getMessage());
            return array(
                'timezone' => self::DEFAULT_TIMEZONE,
                'error' => $e->getMessage()
            );
        }
    }
}

/**
 * Función helper global para compatibilidad
 * 
 * @param string $format Formato de fecha (por defecto: 'd/m/Y H:i:s')
 * @return string Fecha en zona horaria de Costa Rica
 */
function get_costa_rica_datetime($format = 'd/m/Y H:i:s')
{
    return Xpsocial_Timezone::get_costa_rica_datetime($format);
}
