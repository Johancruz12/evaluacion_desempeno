<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use App\Models\EvaluationCriteria;
use App\Models\EvaluationSection;
use App\Models\EvaluationTemplate;
use App\Models\Permission;
use App\Models\Person;
use App\Models\PositionType;
use App\Models\Role;
use App\Models\ScoringRange;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Estados (DANE) ──
        $cundinamarca = State::create(['description' => 'Cundinamarca', 'code' => '25']);
        $antioquia    = State::create(['description' => 'Antioquia', 'code' => '05']);
        $valle        = State::create(['description' => 'Valle del Cauca', 'code' => '76']);
        $atlantico    = State::create(['description' => 'Atlántico', 'code' => '08']);

        // ── Ciudades (DANE) ──
        $bogota       = City::create(['description' => 'Bogotá D.C.', 'code' => '11001', 'state_id' => $cundinamarca->id]);
        $medellin     = City::create(['description' => 'Medellín', 'code' => '05001', 'state_id' => $antioquia->id]);
        $cali         = City::create(['description' => 'Cali', 'code' => '76001', 'state_id' => $valle->id]);
        $barranquilla = City::create(['description' => 'Barranquilla', 'code' => '08001', 'state_id' => $atlantico->id]);

        // ── Roles ──
        $directorRh = Role::create(['description' => 'Administrador', 'slug' => 'director_rh']);
        $jefeArea   = Role::create(['description' => 'Jefe de Área',  'slug' => 'jefe_area']);
        $empleado   = Role::create(['description' => 'Empleado',      'slug' => 'empleado']);

        // ── Permisos ──
        $permisosSeed = [
            'gestionar_usuarios'   => 'Gestionar usuarios del sistema',
            'gestionar_areas'      => 'Gestionar áreas',
            'gestionar_plantillas' => 'Gestionar plantillas de evaluación',
            'crear_evaluaciones'   => 'Crear evaluaciones',
            'ver_evaluaciones'     => 'Ver evaluaciones',
            'completar_evaluaciones' => 'Completar evaluaciones',
            'autoevaluacion'       => 'Realizar autoevaluación',
            'ver_reportes'         => 'Ver reportes generales',
        ];

        $perms = [];
        foreach ($permisosSeed as $slug => $desc) {
            $perms[$slug] = Permission::create(['description' => $desc, 'slug' => $slug]);
        }

        $directorRh->permissions()->sync(array_map(fn ($p) => $p->id, array_filter($perms)));
        $jefeArea->permissions()->sync([$perms['crear_evaluaciones']->id, $perms['ver_evaluaciones']->id, $perms['completar_evaluaciones']->id]);
        $empleado->permissions()->sync([$perms['ver_evaluaciones']->id, $perms['autoevaluacion']->id]);

        // ── Áreas ──
        $areaAdmin  = Area::create(['name' => 'Administración', 'description' => 'Área de administración general', 'is_active' => true]);
        $areaTI     = Area::create(['name' => 'Tecnología de la Información', 'description' => 'Área de TI y sistemas', 'is_active' => true]);
        $areaVentas = Area::create(['name' => 'Ventas', 'description' => 'Área de ventas y comercial', 'is_active' => true]);
        $areaRH     = Area::create(['name' => 'Recursos Humanos', 'description' => 'Área de recursos humanos', 'is_active' => true]);

        // ── Tipos de cargo ──
        $puestoAsistAdmin = PositionType::create(['name' => 'Asistente Administrativo', 'area_id' => $areaAdmin->id, 'is_active' => true]);
        $puestoDevJr      = PositionType::create(['name' => 'Desarrollador Jr.',        'area_id' => $areaTI->id,     'is_active' => true]);
        $puestoDevSr      = PositionType::create(['name' => 'Desarrollador Sr.',        'area_id' => $areaTI->id,     'is_active' => true]);
        $puestoVendedor   = PositionType::create(['name' => 'Vendedor',                 'area_id' => $areaVentas->id, 'is_active' => true]);
        $puestoAnalistaRH = PositionType::create(['name' => 'Analista de R.H.',         'area_id' => $areaRH->id,     'is_active' => true]);

        // ── Personas y Usuarios ──

        // ⭐ ADMIN PRINCIPAL — Cédula Salomón (login = cédula, password = cédula)
        $personAdmin = Person::create([
            'document_number' => '1070588425',
            'document_type'   => 'CC',
            'first_name'      => 'Administrador',
            'last_name'       => 'Salomón',
            'email'           => 'admin.salomon@empresa.com',
            'phone'           => '3000000000',
            'city_id'         => $bogota->id,
        ]);
        $userAdmin = User::create([
            'login'                => '1070588425',
            'password'             => '1070588425', // cast 'hashed' encripta automáticamente
            'person_id'            => $personAdmin->id,
            'area_id'              => $areaRH->id,
            'position_type_id'     => $puestoAnalistaRH->id,
            'employee_code'        => 'ADM-001',
            'is_active'            => true,
            'must_change_password' => true,
        ]);
        $userAdmin->roles()->attach($directorRh->id);

        $personDirector = Person::create(['document_number' => '1001001001', 'document_type' => 'CC', 'first_name' => 'Laura', 'last_name' => 'Martínez', 'email' => 'laura.martinez@empresa.com', 'phone' => '3001234567', 'city_id' => $bogota->id]);
        $userDirector = User::create(['login' => 'director', 'password' => Hash::make('password'), 'person_id' => $personDirector->id, 'area_id' => $areaRH->id, 'position_type_id' => $puestoAnalistaRH->id, 'employee_code' => 'EMP-001']);
        $userDirector->roles()->attach($directorRh->id);

        $personJefeTI = Person::create(['document_number' => '1002002002', 'document_type' => 'CC', 'first_name' => 'Andrés', 'last_name' => 'Ramírez', 'email' => 'andres.ramirez@empresa.com', 'phone' => '3109876543', 'city_id' => $medellin->id]);
        $userJefeTI = User::create(['login' => 'jefe.ti', 'password' => Hash::make('password'), 'person_id' => $personJefeTI->id, 'area_id' => $areaTI->id, 'position_type_id' => $puestoDevSr->id, 'employee_code' => 'EMP-002']);
        $userJefeTI->roles()->attach($jefeArea->id);

        $personJefeVentas = Person::create(['document_number' => '1003003003', 'document_type' => 'CC', 'first_name' => 'Patricia', 'last_name' => 'Gómez', 'email' => 'patricia.gomez@empresa.com', 'phone' => '3205551234', 'city_id' => $cali->id]);
        $userJefeVentas = User::create(['login' => 'jefe.ventas', 'password' => Hash::make('password'), 'person_id' => $personJefeVentas->id, 'area_id' => $areaVentas->id, 'position_type_id' => $puestoVendedor->id, 'employee_code' => 'EMP-003']);
        $userJefeVentas->roles()->attach($jefeArea->id);

        $personCarlos = Person::create(['document_number' => '1004004004', 'document_type' => 'CC', 'first_name' => 'Carlos', 'last_name' => 'Pérez', 'email' => 'carlos.perez@empresa.com', 'phone' => '3151112233', 'city_id' => $bogota->id]);
        $userCarlos = User::create(['login' => 'carlos.perez', 'password' => Hash::make('password'), 'person_id' => $personCarlos->id, 'area_id' => $areaTI->id, 'position_type_id' => $puestoDevJr->id, 'employee_code' => 'EMP-004']);
        $userCarlos->roles()->attach($empleado->id);

        $personMaria = Person::create(['document_number' => '1005005005', 'document_type' => 'CC', 'first_name' => 'María', 'last_name' => 'López', 'email' => 'maria.lopez@empresa.com', 'phone' => '3174445566', 'city_id' => $barranquilla->id]);
        $userMaria = User::create(['login' => 'maria.lopez', 'password' => Hash::make('password'), 'person_id' => $personMaria->id, 'area_id' => $areaTI->id, 'position_type_id' => $puestoDevSr->id, 'employee_code' => 'EMP-005']);
        $userMaria->roles()->attach($empleado->id);

        $personAna = Person::create(['document_number' => '1006006006', 'document_type' => 'CC', 'first_name' => 'Ana', 'last_name' => 'García', 'email' => 'ana.garcia@empresa.com', 'phone' => '3187778899', 'city_id' => $bogota->id]);
        $userAna = User::create(['login' => 'ana.garcia', 'password' => Hash::make('password'), 'person_id' => $personAna->id, 'area_id' => $areaVentas->id, 'position_type_id' => $puestoVendedor->id, 'employee_code' => 'EMP-006']);
        $userAna->roles()->attach($empleado->id);

        // ── Plantilla 1: Desarrollador Jr. (Escala 1–5, 22 criterios → máx 110 puntos) ──
        $template1 = EvaluationTemplate::create(['name' => 'Evaluación Desarrollador Jr. — TI', 'position_type_id' => $puestoDevJr->id, 'is_active' => true]);

        // Sección A: Competencias organizacionales (5 criterios × 5 = 25 pts)
        $secOrg1 = EvaluationSection::create(['template_id' => $template1->id, 'name' => 'Competencias Organizacionales', 'type' => 'competencias_org', 'description' => 'Competencias aplicables a todos los colaboradores', 'weight' => 25, 'order' => 1]);
        EvaluationCriteria::create(['section_id' => $secOrg1->id, 'name' => 'Calidez en la atención',      'description' => 'Trato amable, respetuoso y empático con compañeros y clientes', 'max_score' => 5, 'order' => 1]);
        EvaluationCriteria::create(['section_id' => $secOrg1->id, 'name' => 'Sentido de pertenencia',      'description' => 'Identificación y compromiso con la organización y sus valores', 'max_score' => 5, 'order' => 2]);
        EvaluationCriteria::create(['section_id' => $secOrg1->id, 'name' => 'Cordialidad',                 'description' => 'Mantiene un ambiente armónico y relaciones positivas en el equipo', 'max_score' => 5, 'order' => 3]);
        EvaluationCriteria::create(['section_id' => $secOrg1->id, 'name' => 'Compromiso',                  'description' => 'Cumple con sus responsabilidades y va más allá de lo requerido', 'max_score' => 5, 'order' => 4]);
        EvaluationCriteria::create(['section_id' => $secOrg1->id, 'name' => 'Orientación a resultados',   'description' => 'Enfoca sus esfuerzos en alcanzar los objetivos organizacionales', 'max_score' => 5, 'order' => 5]);

        // Sección B: Competencias del cargo — Desarrollador Jr. (14 criterios × 5 = 70 pts)
        $secCargo1 = EvaluationSection::create(['template_id' => $template1->id, 'name' => 'Competencias del Cargo', 'type' => 'competencias_cargo', 'description' => 'Competencias específicas del rol de Desarrollador Jr.', 'weight' => 65, 'order' => 2]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Seguimiento de instrucciones', 'description' => 'Capacidad para recibir, entender y ejecutar instrucciones técnicas', 'max_score' => 5, 'order' => 1]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Trabajo en equipo',            'description' => 'Colaboración activa con el equipo de desarrollo', 'max_score' => 5, 'order' => 2]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Sentido de urgencia',          'description' => 'Prioriza tareas críticas y responde con agilidad ante incidentes', 'max_score' => 5, 'order' => 3]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Organización',                 'description' => 'Gestiona su tiempo y tareas de forma eficiente', 'max_score' => 5, 'order' => 4]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Atención al detalle',          'description' => 'Verifica la calidad y precisión de su trabajo', 'max_score' => 5, 'order' => 5]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Aprendizaje continuo',         'description' => 'Actitud proactiva hacia la actualización técnica y nuevos conocimientos', 'max_score' => 5, 'order' => 6]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Confidencialidad',             'description' => 'Manejo responsable de la información sensible y datos del cliente', 'max_score' => 5, 'order' => 7]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Manejo del tiempo',            'description' => 'Entrega sus desarrollos dentro de los plazos establecidos', 'max_score' => 5, 'order' => 8]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Comunicación efectiva',        'description' => 'Expresa ideas técnicas con claridad ante el equipo y stakeholders', 'max_score' => 5, 'order' => 9]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Relaciones interpersonales',   'description' => 'Mantiene relaciones profesionales saludables en el equipo', 'max_score' => 5, 'order' => 10]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Autocontrol',                  'description' => 'Mantiene la calma y profesionalismo ante situaciones de presión', 'max_score' => 5, 'order' => 11]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Optimización de recursos',     'description' => 'Uso eficiente de herramientas, infraestructura y tiempo', 'max_score' => 5, 'order' => 12]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Innovación',                   'description' => 'Propone mejoras y soluciones creativas a los problemas técnicos', 'max_score' => 5, 'order' => 13]);
        EvaluationCriteria::create(['section_id' => $secCargo1->id, 'name' => 'Productividad',                'description' => 'Volumen y calidad de entregables en el período evaluado', 'max_score' => 5, 'order' => 14]);

        // Sección C: Responsabilidades (3 criterios × 5 = 15 pts)
        $secResp1 = EvaluationSection::create(['template_id' => $template1->id, 'name' => 'Cumplimiento de Responsabilidades', 'type' => 'responsabilidades', 'description' => 'Cumplimiento de normas y responsabilidades del puesto', 'weight' => 10, 'order' => 3]);
        EvaluationCriteria::create(['section_id' => $secResp1->id, 'name' => 'Puntualidad',              'description' => 'Asistencia y puntualidad en horario laboral y reuniones', 'max_score' => 5, 'order' => 1]);
        EvaluationCriteria::create(['section_id' => $secResp1->id, 'name' => 'Cumplimiento de normas',   'description' => 'Respeto al reglamento interno y políticas de la empresa', 'max_score' => 5, 'order' => 2]);
        EvaluationCriteria::create(['section_id' => $secResp1->id, 'name' => 'Uso de equipos y EPP',     'description' => 'Uso correcto de equipos de trabajo y elementos de protección', 'max_score' => 5, 'order' => 3]);

        // Sección D: Rango (informacional)
        $secRango1 = EvaluationSection::create(['template_id' => $template1->id, 'name' => 'Tabla de Rangos', 'type' => 'rango', 'description' => 'Escala de interpretación del puntaje total', 'weight' => 0, 'order' => 4]);

        // Scoring ranges (basados en total 110 pts)
        ScoringRange::create(['template_id' => $template1->id, 'min_score' => 91,  'max_score' => 110, 'label' => 'Sobrepasa las expectativas', 'color' => '#22C55E']);
        ScoringRange::create(['template_id' => $template1->id, 'min_score' => 71,  'max_score' => 90,  'label' => 'Buen desempeño',             'color' => '#3B82F6']);
        ScoringRange::create(['template_id' => $template1->id, 'min_score' => 50,  'max_score' => 70,  'label' => 'Cumple las expectativas',    'color' => '#EAB308']);
        ScoringRange::create(['template_id' => $template1->id, 'min_score' => 0,   'max_score' => 49,  'label' => 'Requiere mejora',            'color' => '#EF4444']);

        // ── Plantilla 2: Vendedor (misma estructura 22 criterios) ──
        $template2 = EvaluationTemplate::create(['name' => 'Evaluación Vendedor — Área Ventas', 'position_type_id' => $puestoVendedor->id, 'is_active' => true]);

        $secOrg2 = EvaluationSection::create(['template_id' => $template2->id, 'name' => 'Competencias Organizacionales', 'type' => 'competencias_org', 'description' => 'Competencias aplicables a todos los colaboradores', 'weight' => 25, 'order' => 1]);
        EvaluationCriteria::create(['section_id' => $secOrg2->id, 'name' => 'Calidez en la atención',    'description' => 'Trato amable, respetuoso y empático con clientes y compañeros', 'max_score' => 5, 'order' => 1]);
        EvaluationCriteria::create(['section_id' => $secOrg2->id, 'name' => 'Sentido de pertenencia',    'description' => 'Identificación y compromiso con la organización', 'max_score' => 5, 'order' => 2]);
        EvaluationCriteria::create(['section_id' => $secOrg2->id, 'name' => 'Cordialidad',               'description' => 'Ambiente armónico y relaciones positivas', 'max_score' => 5, 'order' => 3]);
        EvaluationCriteria::create(['section_id' => $secOrg2->id, 'name' => 'Compromiso',                'description' => 'Cumple con sus responsabilidades de venta', 'max_score' => 5, 'order' => 4]);
        EvaluationCriteria::create(['section_id' => $secOrg2->id, 'name' => 'Orientación a resultados',  'description' => 'Alcanza y supera las metas comerciales', 'max_score' => 5, 'order' => 5]);

        $secCargo2 = EvaluationSection::create(['template_id' => $template2->id, 'name' => 'Competencias del Cargo', 'type' => 'competencias_cargo', 'description' => 'Competencias específicas del rol de Vendedor', 'weight' => 65, 'order' => 2]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Orientación al cliente',     'description' => 'Entiende y satisface las necesidades del cliente', 'max_score' => 5, 'order' => 1]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Negociación',                'description' => 'Habilidad para concretar acuerdos beneficiosos', 'max_score' => 5, 'order' => 2]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Conocimiento del producto',  'description' => 'Dominio completo del portafolio de productos y servicios', 'max_score' => 5, 'order' => 3]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Cumplimiento de metas',      'description' => 'Alcance de los objetivos de venta mensuales', 'max_score' => 5, 'order' => 4]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Prospección de clientes',    'description' => 'Generación activa de nuevos prospectos y clientes', 'max_score' => 5, 'order' => 5]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Seguimiento post-venta',     'description' => 'Fidelización y atención después de la venta', 'max_score' => 5, 'order' => 6]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Comunicación efectiva',      'description' => 'Transmite mensajes de venta claros y convincentes', 'max_score' => 5, 'order' => 7]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Manejo del tiempo',          'description' => 'Organiza sus visitas y gestiones comerciales eficientemente', 'max_score' => 5, 'order' => 8]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Relaciones interpersonales', 'description' => 'Construye relaciones duraderas con clientes y aliados', 'max_score' => 5, 'order' => 9]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Autocontrol',                'description' => 'Maneja objeciones y rechazos con profesionalismo', 'max_score' => 5, 'order' => 10]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Innovación',                 'description' => 'Propone nuevas estrategias y canales de venta', 'max_score' => 5, 'order' => 11]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Productividad',              'description' => 'Volumen de ventas y eficiencia en la gestión comercial', 'max_score' => 5, 'order' => 12]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Organización',               'description' => 'Orden en la gestión de carteras y CRM', 'max_score' => 5, 'order' => 13]);
        EvaluationCriteria::create(['section_id' => $secCargo2->id, 'name' => 'Sentido de urgencia',        'description' => 'Responde rápidamente a oportunidades y solicitudes de clientes', 'max_score' => 5, 'order' => 14]);

        $secResp2 = EvaluationSection::create(['template_id' => $template2->id, 'name' => 'Cumplimiento de Responsabilidades', 'type' => 'responsabilidades', 'description' => 'Responsabilidades generales del puesto', 'weight' => 10, 'order' => 3]);
        EvaluationCriteria::create(['section_id' => $secResp2->id, 'name' => 'Puntualidad',            'description' => 'Asistencia y puntualidad en sus compromisos', 'max_score' => 5, 'order' => 1]);
        EvaluationCriteria::create(['section_id' => $secResp2->id, 'name' => 'Cumplimiento de normas', 'description' => 'Respeto al reglamento interno y políticas de la empresa', 'max_score' => 5, 'order' => 2]);
        EvaluationCriteria::create(['section_id' => $secResp2->id, 'name' => 'Uso de recursos',        'description' => 'Uso correcto de equipos, vehículos y recursos asignados', 'max_score' => 5, 'order' => 3]);

        EvaluationSection::create(['template_id' => $template2->id, 'name' => 'Tabla de Rangos', 'type' => 'rango', 'description' => 'Escala de interpretación', 'weight' => 0, 'order' => 4]);

        ScoringRange::create(['template_id' => $template2->id, 'min_score' => 91, 'max_score' => 110, 'label' => 'Sobrepasa las expectativas', 'color' => '#22C55E']);
        ScoringRange::create(['template_id' => $template2->id, 'min_score' => 71, 'max_score' => 90,  'label' => 'Buen desempeño',             'color' => '#3B82F6']);
        ScoringRange::create(['template_id' => $template2->id, 'min_score' => 50, 'max_score' => 70,  'label' => 'Cumple las expectativas',    'color' => '#EAB308']);
        ScoringRange::create(['template_id' => $template2->id, 'min_score' => 0,  'max_score' => 49,  'label' => 'Requiere mejora',            'color' => '#EF4444']);

        // ── Plantilla 3: Personal Técnico, Auxiliares, Asistentes y Operativos (SP-GETH-FO-25) ──
        // Estructura exacta del formulario de Clínica Junical: 22 criterios × 5 = máx 110 puntos
        $template3 = EvaluationTemplate::create([
            'name' => 'Evaluación Personal Técnico, Auxiliares, Asistentes y Operativos (SP-GETH-FO-25)',
            'description' => 'Evaluación de desempeño para Personal Técnico, Auxiliares, Asistentes y Operativos. Cód.: SP-GETH-FO-25, Versión: 01, Fecha Vigente: 28-03-2025.',
            'position_type_id' => null,
            'is_active' => true,
        ]);

        // Asignar a todas las áreas (global: aplica a técnicos/auxiliares/asistentes/operativos de cualquier área)
        $template3->areas()->sync([$areaAdmin->id, $areaTI->id, $areaVentas->id, $areaRH->id]);

        // ── Sección 3: Competencias Organizacionales (5 criterios × 5 = 25 pts) ──
        $secOrg3 = EvaluationSection::create([
            'template_id' => $template3->id,
            'name' => 'Competencias Organizacionales',
            'type' => 'competencias_org',
            'description' => 'Competencias aplicables a todos los colaboradores de la organización',
            'weight' => 25,
            'order' => 1,
        ]);

        EvaluationCriteria::create(['section_id' => $secOrg3->id, 'name' => 'Calidez en la Atención', 'description' => 'Es la capacidad para entender, comprender y atender las necesidades, problemas o situaciones de los usuarios y/o colaboradores, con el fin de brindar una solución benéfica para él y la organización.', 'max_score' => 5, 'order' => 1]);
        EvaluationCriteria::create(['section_id' => $secOrg3->id, 'name' => 'Sentido de Pertenencia', 'description' => 'Facilidad para alinear los valores e intereses personales con los objetivos, valores y políticas de la empresa, con el fin de divulgarlos y aplicarlos eficazmente en el cargo o el área de trabajo.', 'max_score' => 5, 'order' => 2]);
        EvaluationCriteria::create(['section_id' => $secOrg3->id, 'name' => 'Cordialidad en el Trabajo', 'description' => 'Mantener una actitud personal y laboral dispuesta y cordial. Buscar en las relaciones de trabajo que el cargo demande una posición que facilite el respeto, el buen trato y la amabilidad.', 'max_score' => 5, 'order' => 3]);
        EvaluationCriteria::create(['section_id' => $secOrg3->id, 'name' => 'Compromiso', 'description' => 'Esfuerzo permanente hacia la consecución de un objetivo, lo cual implica un alto grado de integración de la disposición física, emocional e intelectual de un sujeto sobre lo que desea conseguir, sea a beneficio propio o común.', 'max_score' => 5, 'order' => 4]);
        EvaluationCriteria::create(['section_id' => $secOrg3->id, 'name' => 'Orientación a Resultados', 'description' => 'Mantener e incrementar altos niveles de ejecución en el trabajo con el fin de buscar, perseguir y lograr los objetivos y metas de alto estándar, trazados por la organización.', 'max_score' => 5, 'order' => 5]);

        // ── Sección 4: Competencias del Cargo (14 criterios × 5 = 70 pts) ──
        $secCargo3 = EvaluationSection::create([
            'template_id' => $template3->id,
            'name' => 'Competencias del Cargo',
            'type' => 'competencias_cargo',
            'description' => 'Competencias específicas del cargo para Personal Técnico, Auxiliares, Asistentes y Operativos',
            'weight' => 70,
            'order' => 2,
        ]);

        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Seguimiento de Instrucciones', 'description' => 'Capacidad para acatar y ejecutar las tareas y proyectos del área de trabajo en el tiempo asignado y con alta calidad en el resultado final.', 'max_score' => 5, 'order' => 1]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Trabajo en Equipo', 'description' => 'Construir grupos de trabajo efectivos para la consecución de objetivos y metas comunes teniendo en cuenta la planeación estratégica de la organización, fomentando un ambiente de colaboración y coordinación.', 'max_score' => 5, 'order' => 2]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Sentido de Urgencia', 'description' => 'Es la capacidad de actuar y adueñarse de una situación de manera efectiva, rápida y oportuna.', 'max_score' => 5, 'order' => 3]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Organización', 'description' => 'Capacidad para organizar el trabajo de una forma correcta aprovechando al máximo el tiempo laboral del que se dispone.', 'max_score' => 5, 'order' => 4]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Atención al Detalle', 'description' => 'Capacidad que se asocia con la excelencia, la cual se caracteriza por la conciencia plena durante la ejecución de cada tarea, garantizando resultados de alta calidad.', 'max_score' => 5, 'order' => 5]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Aprendizaje Continuo', 'description' => 'Capacidad para asimilar y aplicar nueva información de una manera eficaz y efectiva en el cargo, que brinde de alguna manera herramientas significativas de trabajo.', 'max_score' => 5, 'order' => 6]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Confidencialidad', 'description' => 'Es la capacidad de resguardar y proteger con discreción la información a la cual tienen acceso en el ejercicio de su cargo.', 'max_score' => 5, 'order' => 7]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Manejo del Tiempo', 'description' => 'Administrar de forma eficiente el horario de trabajo, de tal forma que alcanza a cumplir con lo planeado.', 'max_score' => 5, 'order' => 8]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Comunicación Efectiva', 'description' => 'Escucha activamente a otros, entender sus solicitudes y expresar verbalmente y/o por escrito conceptos e ideas en forma efectiva, solicitar retroalimentación.', 'max_score' => 5, 'order' => 9]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Relaciones Interpersonales', 'description' => 'Facilidad para establecer y mantener contactos, tanto a nivel interno como externo, destacándose por la cordialidad y respeto.', 'max_score' => 5, 'order' => 10]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Autocontrol', 'description' => 'Capacidad de mantener las propias emociones bajo control y evitar reacciones negativas ante provocaciones, oposición u hostilidad por parte de otros, o bajo condiciones de estrés.', 'max_score' => 5, 'order' => 11]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Optimización de Recursos', 'description' => 'Adaptar los procesos para optimizar sus parámetros, pero sin infringir sus límites. Generalmente, tiene como objetivos minimizar costos y maximizar el rendimiento, la productividad y la eficiencia.', 'max_score' => 5, 'order' => 12]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Innovación', 'description' => 'Implica idear soluciones nuevas y diferentes ante problemas o situaciones requeridos por el propio puesto, la organización, los clientes o el segmento de la economía donde actúe.', 'max_score' => 5, 'order' => 13]);
        EvaluationCriteria::create(['section_id' => $secCargo3->id, 'name' => 'Productividad', 'description' => 'Dinamismo y energía a la hora de realizar su trabajo, procurando siempre cumplir con las metas propuestas dentro de los límites establecidos por la organización.', 'max_score' => 5, 'order' => 14]);

        // ── Sección 5: Cumplimiento de Responsabilidades (3 criterios × 5 = 15 pts) ──
        $secResp3 = EvaluationSection::create([
            'template_id' => $template3->id,
            'name' => 'Cumplimiento de Responsabilidades',
            'type' => 'responsabilidades',
            'description' => 'Competencias asociadas al compromiso con el que las personas realizan las tareas encomendadas. Su preocupación por el cumplimiento de las normas, políticas, protocolos y funciones asignadas en la organización.',
            'weight' => 15,
            'order' => 3,
        ]);

        EvaluationCriteria::create(['section_id' => $secResp3->id, 'name' => 'Puntualidad', 'description' => 'Es la capacidad que tiene el empleado para acatar su horario de trabajo y dar cumplimiento estricto a este.', 'max_score' => 5, 'order' => 1]);
        EvaluationCriteria::create(['section_id' => $secResp3->id, 'name' => 'Aceptación de Normas, Protocolos, Políticas Organizacionales, Ambientales y del Sistema de Seguridad y Salud en el Trabajo', 'description' => 'Disposición para entender, acatar y actuar dentro de las directrices y normas organizacionales establecidas.', 'max_score' => 5, 'order' => 2]);
        EvaluationCriteria::create(['section_id' => $secResp3->id, 'name' => 'Porte del Uniforme e Implementos de Protección Personal', 'description' => 'Porta de forma adecuada el uniforme y utiliza los elementos de protección personal.', 'max_score' => 5, 'order' => 3]);

        // ── Sección: Tabla de Rangos (informacional) ──
        EvaluationSection::create([
            'template_id' => $template3->id,
            'name' => 'Tabla de Rangos',
            'type' => 'rango',
            'description' => 'Escala de interpretación del puntaje total',
            'weight' => 0,
            'order' => 4,
        ]);

        // Scoring ranges (basados en total 110 pts — escala del formulario SP-GETH-FO-25)
        ScoringRange::create(['template_id' => $template3->id, 'min_score' => 91,  'max_score' => 110, 'label' => 'Sobre pasa las expectativas', 'color' => '#22C55E']);
        ScoringRange::create(['template_id' => $template3->id, 'min_score' => 71,  'max_score' => 90,  'label' => 'Se destaca por su buen desempeño', 'color' => '#3B82F6']);
        ScoringRange::create(['template_id' => $template3->id, 'min_score' => 50,  'max_score' => 70,  'label' => 'Cumple con lo esperado', 'color' => '#EAB308']);
        ScoringRange::create(['template_id' => $template3->id, 'min_score' => 0,   'max_score' => 49,  'label' => 'No cumple con todos los requerimientos del cargo, requiere plan de mejoramiento de inmediato', 'color' => '#EF4444']);
    }
}
