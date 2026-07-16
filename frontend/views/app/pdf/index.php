<?php
require_once '../../../core/db.php';

if (!isset($_GET['id'])) {
    die("ID de inscripción no proporcionado.");
}

$inscripcion_id = $_GET['id'];

try {
    //Aca se consulta para unificar los datos esenciales del estudiante y su inscripción
    $stmt = $db->prepare("
        SELECT i.*, e.*, g.nombre AS grado, s.nombre AS seccion, p.nombre AS periodo
        FROM inscripciones i
        INNER JOIN estudiantes e ON i.estudiante_id = e.id
        INNER JOIN grados g ON i.grado_asignado_id = g.id
        INNER JOIN secciones s ON i.seccion_asignada_id = s.id
        INNER JOIN periodos_academicos p ON i.periodo_id = p.id
        WHERE i.id = :id LIMIT 1
    ");
    $stmt->execute(['id' => $inscripcion_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        die("Inscripción no encontrada.");
    }

    //Esto es para buscar los datos complementarios del Representante y la Dirección asignada
    $stmtRep = $db->prepare("SELECT * FROM representantes WHERE id = :id LIMIT 1");
    $stmtRep->execute(['id' => $data['representante_principal_id']]);
    $representante = $stmtRep->fetch(PDO::FETCH_ASSOC);

    $direccion = null;
    if ($representante) {
        $stmtDir = $db->prepare("SELECT * FROM direcciones WHERE id = :id LIMIT 1");
        $stmtDir->execute(['id' => $representante['direccion_id']]);
        $direccion = $stmtDir->fetch(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla_Inscripcion_<?php echo $data['cedula_identidad'] ?? $data['cedula_escolar']; ?></title>
    <link rel="stylesheet" href="../../../assets/bootstrap/css/bootstrap.min.css">
    <style>
        body { font-size: 14px; background-color: #fff; color: #000; }
        .planilla-header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .seccion-titulo { background-color: #f2f2f2; font-weight: bold; padding: 5px 10px; margin-top: 15px; border: 1px solid #ddd; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

<div class="container my-4">
    <div class="no-print d-flex justify-content-between mb-4 bg-light p-3 rounded shadow-sm">
        <span class="text-muted d-flex align-items-center">Vista previa del documento oficial</span>
        <button onclick="window.print();" class="btn btn-danger font-weight-bold shadow-sm">
            🖨️ Guardar como PDF / Imprimir Planilla
        </button>
    </div>

    <div class="planilla-header text-center">
        <h5 class="m-0 fw-bold">REPÚBLICA BOLIVARIANA DE VENEZUELA</h5>
        <h6 class="m-0 text-secondary">U.E. FERNANDO PEÑALVER</h6>
        <h4 class="fw-bold mt-2 text-dark">PLANILLA OFICIAL DE INSCRIPCIÓN</h4>
        <span class="badge bg-dark">Periodo Académico: <?php echo htmlspecialchars($data['periodo']); ?></span>
    </div>

    <div class="seccion-titulo">1. DATOS ESCOLARES ASIGNADOS</div>
    <div class="row p-2 g-2">
        <div class="col-4"><strong>Nivel Académico:</strong> <?php echo htmlspecialchars($data['nivel_academico']); ?></div>
        <div class="col-4"><strong>Grado:</strong> <?php echo htmlspecialchars($data['grado']); ?></div>
        <div class="col-4"><strong>Sección:</strong> <?php echo htmlspecialchars($data['seccion']); ?></div>
        <div class="col-6"><strong>Tipo de Ingreso:</strong> <?php echo htmlspecialchars($data['tipo_ingreso']); ?></div>
        <div class="col-6"><strong>Colegio de Procedencia:</strong> <?php echo htmlspecialchars($data['colegio_procedencia'] ?? 'Ninguno'); ?></div>
    </div>

    <div class="seccion-titulo">2. DATOS PERSONALES DEL ESTUDIANTE</div>
    <div class="row p-2 g-2">
        <div class="col-6"><strong>Nombres:</strong> <?php echo htmlspecialchars($data['nombres']); ?></div>
        <div class="col-6"><strong>Apellidos:</strong> <?php echo htmlspecialchars($data['apellidos']); ?></div>
        <div class="col-4"><strong>Cédula Identidad:</strong> <?php echo $data['cedula_identidad'] ?  $data['nacionalidad'].'-'.$data['cedula_identidad'] : 'No posee'; ?></div>
        <div class="col-4"><strong>Cédula Escolar:</strong> <?php echo htmlspecialchars($data['cedula_escolar']); ?></div>
        <div class="col-4"><strong>Fecha de Nac.:</strong> <?php echo htmlspecialchars($data['fecha_nacimiento']); ?></div>
        <div class="col-4"><strong>Género:</strong> <?php echo htmlspecialchars($data['sexo']); ?></div>
        <div class="col-4"><strong>Lateralidad:</strong> <?php echo htmlspecialchars($data['lateralidad']); ?></div>
    </div>

    <div class="seccion-titulo">3. INFORMACIÓN DEL REPRESENTANTE LEGAL</div>
    <?php if ($representante): ?>
    <div class="row p-2 g-2">
        <div class="col-6"><strong>Nombre y Apellido:</strong> <?php echo htmlspecialchars($representante['nombres'] . ' ' . $representante['apellidos']); ?></div>
        <div class="col-6"><strong>Documento de Identidad:</strong> <?php echo htmlspecialchars($representante['nacionalidad'] . '-' . $representante['cedula']); ?></div>
        <div class="col-6"><strong>Parentesco:</strong> <?php echo htmlspecialchars($representante['parentesco']); ?></div>
        <div class="col-6"><strong>Teléfono:</strong> <?php echo htmlspecialchars($representante['telefono'] ?? 'No registrado'); ?></div>
    </div>
    <?php else: ?>
    <p class="text-muted p-2 m-0">No se encontraron datos registrados del representante principal.</p>
    <?php endif; ?>

    <div class="seccion-titulo">4. DIRECCIÓN DE HABITACIÓN</div>
    <?php if ($direccion): ?>
    <div class="row p-2 g-2">
        <div class="col-12"><strong>Dirección Completa:</strong> <?php echo htmlspecialchars('Ubicacion'); ?></div>
        <div class="col-6"><strong>Tipo de Vivienda:</strong> <?php echo htmlspecialchars($direccion['tipo_vivienda']); ?></div>
        <div class="col-6"><strong>Condición de Vivienda:</strong> <?php echo htmlspecialchars($direccion['condicion_vivienda']); ?></div>
    </div>
    <?php else: ?>
    <p class="text-muted p-2 m-0">No hay detalles de dirección guardados para esta inscripción.</p>
    <?php endif; ?>

    <div class="row mt-5 pt-4 text-center">
        <div class="col-6">
            <div class="mx-auto" style="border-top: 1px solid #000; width: 200px;"></div>
            <p class="small mt-2">Firma del Representante</p>
        </div>
        <div class="col-6">
            <div class="mx-auto" style="border-top: 1px solid #000; width: 200px;"></div>
            <p class="small mt-2">Sello y Firma Control de Estudios</p>
        </div>
    </div>
</div>

<script>
    //aqui se lanza automáticamente la ventana nativa de conversión a PDF al cargar la página por comodidad
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => { window.print(); }, 500);
    });
</script>
</body>
</html>