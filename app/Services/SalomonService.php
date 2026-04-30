<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SalomonService
{
    /**
     * Find an employee in Salomón by their cédula (identifica).
     */
    public function findEmployeeByCedula(string $cedula): ?object
    {
        $results = DB::connection('salomon')->select("
            SELECT TOP 1
                t.codigo        AS trabajador_codigo,
                t.organigrama   AS organigrama_codigo,
                h.identifica    AS cedula,
                h.pnombre       AS primer_nombre,
                h.snombre       AS segundo_nombre,
                h.papellido     AS primer_apellido,
                h.sapellido     AS segundo_apellido,
                h.telefono_residencia AS telefono,
                h.direccion_residencia AS direccion,
                h.fnacimiento   AS fecha_nacimiento,
                a.codigo        AS area_codigo,
                a.nombre        AS area_nombre,
                c.codigo        AS cargo_codigo,
                c.nombre        AS cargo_nombre
            FROM tbm.trabajador t
            INNER JOIN per.hoja_vida h ON h.codigo = t.hoja
            INNER JOIN tbm.organigrama o ON o.codigo = t.organigrama
            LEFT JOIN tbm.area a ON a.codigo = o.area
            LEFT JOIN tbm.cargo c ON c.codigo = o.cargo
            INNER JOIN nom.contrato ct ON ct.trabajador = t.codigo AND ct.activo = 1
            WHERE h.identifica = ?
        ", [$cedula]);

        return $results[0] ?? null;
    }

    /**
     * Get all active areas from Salomón.
     */
    public function getAllAreas(): array
    {
        return DB::connection('salomon')->select("
            SELECT codigo, nombre, descripcion, activo
            FROM tbm.area
            WHERE activo = 1
            ORDER BY nombre
        ");
    }

    /**
     * Get all active positions (cargos) from Salomón.
     */
    public function getAllCargos(): array
    {
        return DB::connection('salomon')->select("
            SELECT codigo, nombre, activo
            FROM tbm.cargo
            WHERE activo = 1
            ORDER BY nombre
        ");
    }

    /**
     * Get all active employees with their area and position info.
     */
    public function getActiveEmployees(): array
    {
        return DB::connection('salomon')->select("
            SELECT
                t.codigo        AS trabajador_codigo,
                h.identifica    AS cedula,
                h.pnombre       AS primer_nombre,
                h.snombre       AS segundo_nombre,
                h.papellido     AS primer_apellido,
                h.sapellido     AS segundo_apellido,
                a.codigo        AS area_codigo,
                a.nombre        AS area_nombre,
                c.codigo        AS cargo_codigo,
                c.nombre        AS cargo_nombre
            FROM tbm.trabajador t
            INNER JOIN per.hoja_vida h ON h.codigo = t.hoja
            INNER JOIN tbm.organigrama o ON o.codigo = t.organigrama
            LEFT JOIN tbm.area a ON a.codigo = o.area
            LEFT JOIN tbm.cargo c ON c.codigo = o.cargo
            INNER JOIN nom.contrato ct ON ct.trabajador = t.codigo AND ct.activo = 1
            ORDER BY h.papellido, h.pnombre
        ");
    }

    /**
     * Get active employees from Salomón filtered by area code.
     */
    public function getActiveEmployeesByArea(int $areaCodigo): array
    {
        return DB::connection('salomon')->select(" 
            SELECT
                t.codigo        AS trabajador_codigo,
                h.identifica    AS cedula,
                h.pnombre       AS primer_nombre,
                h.snombre       AS segundo_nombre,
                h.papellido     AS primer_apellido,
                h.sapellido     AS segundo_apellido,
                a.codigo        AS area_codigo,
                a.nombre        AS area_nombre,
                c.codigo        AS cargo_codigo,
                c.nombre        AS cargo_nombre
            FROM tbm.trabajador t
            INNER JOIN per.hoja_vida h ON h.codigo = t.hoja
            INNER JOIN tbm.organigrama o ON o.codigo = t.organigrama
            LEFT JOIN tbm.area a ON a.codigo = o.area
            LEFT JOIN tbm.cargo c ON c.codigo = o.cargo
            INNER JOIN nom.contrato ct ON ct.trabajador = t.codigo AND ct.activo = 1
            WHERE a.codigo = ?
            ORDER BY h.papellido, h.pnombre
        ", [$areaCodigo]);
    }

}
