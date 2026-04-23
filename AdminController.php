<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Sala;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\MailService;
use Core\BaseController;

class AdminController extends BaseController
{
    public function dashboard(): void
    {
        $this->view('admin.dashboard');
    }

    public function calendario(): void
    {
        $this->view('admin.calendario');
    }

    public function citas(): void
    {
        $pdo = \Core\Database::getConnection();
        $stmt = $pdo->query("
            SELECT c.id, c.fecha, c.hora, c.estado, u.nombre AS paciente_nombre, d.nombre AS doctor_nombre, s.nombre AS sala_nombre, suc.nombre AS sucursal_nombre
            FROM citas c
            JOIN users u ON c.paciente_id = u.id
            JOIN doctores d ON c.doctor_id = d.id
            JOIN salas s ON c.sala_id = s.id
            JOIN sucursales suc ON c.sucursal_id = suc.id
            ORDER BY c.fecha DESC, c.hora DESC
            LIMIT 200
        ");
        $citas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($citas as &$c) {
            $c['hora'] = substr($c['hora'], 0, 5);
        }
        $this->view('admin.citas', ['citas' => $citas]);
    }

    public function nuevaCita(): void
    {
        $sucursales = Sucursal::all(true);
        $doctores = Doctor::all(true);
        $salas = Sala::all(true);
        $pacientes = User::allPacientes();
        $horas = Cita::HORAS_VALIDAS;
        $this->view('admin.cita-form', [
            'cita' => null,
            'sucursales' => $sucursales,
            'doctores' => $doctores,
            'salas' => $salas,
            'pacientes' => $pacientes,
            'horas' => $horas,
        ]);
    }

    public function guardarCita(): void
    {
        $this->validarYGuardarCita(null);
    }

    public function editarCita(string $id): void
    {
        $id = (int) $id;
        $cita = Cita::find($id);
        if (!$cita) {
            $_SESSION['error'] = 'Cita no encontrada.';
            $this->redirect('/admin/citas');
            return;
        }
        $cita['hora'] = substr((string) $cita['hora'], 0, 5);
        $sucursales = Sucursal::all(true);
        $doctores = Doctor::all(true);
        $salas = Sala::all(true);
        $pacientes = User::allPacientes();
        $horas = Cita::HORAS_VALIDAS;
        $this->view('admin.cita-form', [
            'cita' => $cita,
            'sucursales' => $sucursales,
            'doctores' => $doctores,
            'salas' => $salas,
            'pacientes' => $pacientes,
            'horas' => $horas,
        ]);
    }

    public function actualizarCita(string $id): void
    {
        $this->validarYGuardarCita((int) $id);
    }

    private function validarYGuardarCita(?int $citaId): void
    {
        $paciente_id = (int) ($_POST['paciente_id'] ?? 0);
        $doctor_id = (int) ($_POST['doctor_id'] ?? 0);
        $sala_id = (int) ($_POST['sala_id'] ?? 0);
        $sucursal_id = (int) ($_POST['sucursal_id'] ?? 0);
        $fecha = trim((string) ($_POST['fecha'] ?? ''));
        $hora = trim((string) ($_POST['hora'] ?? ''));
        $notas = trim((string) ($_POST['notas'] ?? ''));

        $errors = [];
        if ($paciente_id <= 0) $errors[] = 'Seleccione un paciente.';
        if ($doctor_id <= 0) $errors[] = 'Seleccione un doctor.';
        if ($sala_id <= 0) $errors[] = 'Seleccione una sala.';
        if ($sucursal_id <= 0) $errors[] = 'Seleccione una sucursal.';
        if ($fecha === '') $errors[] = 'La fecha es obligatoria.';
        if ($hora === '') $errors[] = 'La hora es obligatoria.';

        if (!in_array($hora, Cita::HORAS_VALIDAS, true)) {
            $errors[] = 'Hora no válida (bloques de 30 min).';
        }

        if ($errors !== []) {
            $_SESSION['error'] = implode(' ', $errors);
            $this->redirect($citaId ? "/admin/citas/{$citaId}/editar" : '/admin/citas/nueva');
            return;
        }

        if (Cita::doctorOcupado($doctor_id, $fecha, $hora, $citaId)) {
            $_SESSION['error'] = 'El doctor ya tiene una cita en ese horario.';
            $this->redirect($citaId ? "/admin/citas/{$citaId}/editar" : '/admin/citas/nueva');
            return;
        }
        if (Cita::salaOcupada($sala_id, $fecha, $hora, $citaId)) {
            $_SESSION['error'] = 'La sala ya está ocupada en ese horario.';
            $this->redirect($citaId ? "/admin/citas/{$citaId}/editar" : '/admin/citas/nueva');
            return;
        }

        $data = [
            'paciente_id' => $paciente_id,
            'doctor_id' => $doctor_id,
            'sala_id' => $sala_id,
            'sucursal_id' => $sucursal_id,
            'fecha' => $fecha,
            'hora' => $hora,
            'notas' => $notas,
        ];

        $mailService = new MailService();
        $paciente = User::find($paciente_id);
        $pacienteEmail = $paciente['email'] ?? '';
        $pacienteNombre = $paciente['nombre'] ?? '';

        if ($citaId) {
            Cita::update($citaId, $data);
            $cita = Cita::find($citaId);
            if ($cita && $pacienteEmail !== '') {
                try {
                    $mailService->enviarActualizacionCita($cita, $pacienteEmail, $pacienteNombre);
                } catch (\Throwable $e) {
                    $_SESSION['warning'] = 'Cita actualizada pero no se pudo enviar el correo: ' . $e->getMessage();
                }
            }
            $_SESSION['success'] = 'Cita actualizada correctamente.';
            $this->redirect('/admin/citas');
        } else {
            $id = Cita::create($data);
            $cita = Cita::find($id);
            if ($cita && $pacienteEmail !== '') {
                try {
                    $mailService->enviarConfirmacionCita($cita, $pacienteEmail, $pacienteNombre);
                } catch (\Throwable $e) {
                    $_SESSION['warning'] = 'Cita creada pero no se pudo enviar el correo: ' . $e->getMessage();
                }
            }
            $_SESSION['success'] = 'Cita creada correctamente.';
            $this->redirect('/admin/citas');
        }
    }

    public function cancelarCita(string $id): void
    {
        $id = (int) $id;
        $cita = Cita::find($id);
        if (!$cita) {
            $_SESSION['error'] = 'Cita no encontrada.';
            $this->redirect('/admin/citas');
            return;
        }
        Cita::cancelar($id);
        $paciente = User::find((int) $cita['paciente_id']);
        if ($paciente) {
            try {
                (new MailService())->enviarCancelacionCita($cita, $paciente['email'], $paciente['nombre']);
            } catch (\Throwable $e) {
                $_SESSION['warning'] = 'Cita cancelada pero no se pudo enviar el correo.';
            }
        }
        $_SESSION['success'] = 'Cita cancelada.';
        $this->redirect('/admin/citas');
    }

    public function doctores(): void
    {
        $doctores = Doctor::all();
        $this->view('admin.doctores', ['doctores' => $doctores]);
    }

    public function guardarDoctor(): void
    {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $especialidad = trim((string) ($_POST['especialidad'] ?? ''));
        $sucursal_id = (int) ($_POST['sucursal_id'] ?? 0);
        if ($nombre === '' || $sucursal_id <= 0) {
            $_SESSION['error'] = 'Nombre y sucursal son obligatorios.';
            $this->redirect('/admin/doctores');
            return;
        }
        Doctor::create(compact('nombre', 'especialidad', 'sucursal_id'));
        $_SESSION['success'] = 'Doctor registrado.';
        $this->redirect('/admin/doctores');
    }

    public function actualizarDoctor(string $id): void
    {
        $id = (int) $id;
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $especialidad = trim((string) ($_POST['especialidad'] ?? ''));
        $sucursal_id = (int) ($_POST['sucursal_id'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;
        if ($nombre === '' || $sucursal_id <= 0) {
            $_SESSION['error'] = 'Nombre y sucursal son obligatorios.';
            $this->redirect('/admin/doctores');
            return;
        }
        Doctor::update($id, compact('nombre', 'especialidad', 'sucursal_id') + ['activo' => $activo]);
        $_SESSION['success'] = 'Doctor actualizado.';
        $this->redirect('/admin/doctores');
    }

    public function eliminarDoctor(string $id): void
    {
        Doctor::delete((int) $id);
        $_SESSION['success'] = 'Doctor eliminado.';
        $this->redirect('/admin/doctores');
    }

    public function salas(): void
    {
        $salas = Sala::all();
        $this->view('admin.salas', ['salas' => $salas]);
    }

    public function guardarSala(): void
    {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $sucursal_id = (int) ($_POST['sucursal_id'] ?? 0);
        if ($nombre === '' || $sucursal_id <= 0) {
            $_SESSION['error'] = 'Nombre y sucursal son obligatorios.';
            $this->redirect('/admin/salas');
            return;
        }
        Sala::create(compact('nombre', 'sucursal_id'));
        $_SESSION['success'] = 'Sala registrada.';
        $this->redirect('/admin/salas');
    }

    public function actualizarSala(string $id): void
    {
        $id = (int) $id;
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $sucursal_id = (int) ($_POST['sucursal_id'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;
        if ($nombre === '' || $sucursal_id <= 0) {
            $_SESSION['error'] = 'Nombre y sucursal son obligatorios.';
            $this->redirect('/admin/salas');
            return;
        }
        Sala::update($id, compact('nombre', 'sucursal_id') + ['activo' => $activo]);
        $_SESSION['success'] = 'Sala actualizada.';
        $this->redirect('/admin/salas');
    }

    public function eliminarSala(string $id): void
    {
        Sala::delete((int) $id);
        $_SESSION['success'] = 'Sala eliminada.';
        $this->redirect('/admin/salas');
    }

    public function sucursales(): void
    {
        $sucursales = Sucursal::all();
        $this->view('admin.sucursales', ['sucursales' => $sucursales]);
    }

    public function guardarSucursal(): void
    {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $direccion = trim((string) ($_POST['direccion'] ?? ''));
        $telefono = trim((string) ($_POST['telefono'] ?? ''));
        if ($nombre === '' || $direccion === '') {
            $_SESSION['error'] = 'Nombre y dirección son obligatorios.';
            $this->redirect('/admin/sucursales');
            return;
        }
        Sucursal::create(compact('nombre', 'direccion', 'telefono'));
        $_SESSION['success'] = 'Sucursal registrada.';
        $this->redirect('/admin/sucursales');
    }

    public function actualizarSucursal(string $id): void
    {
        $id = (int) $id;
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $direccion = trim((string) ($_POST['direccion'] ?? ''));
        $telefono = trim((string) ($_POST['telefono'] ?? ''));
        $activo = isset($_POST['activo']) ? 1 : 0;
        if ($nombre === '' || $direccion === '') {
            $_SESSION['error'] = 'Nombre y dirección son obligatorios.';
            $this->redirect('/admin/sucursales');
            return;
        }
        Sucursal::update($id, compact('nombre', 'direccion', 'telefono') + ['activo' => $activo]);
        $_SESSION['success'] = 'Sucursal actualizada.';
        $this->redirect('/admin/sucursales');
    }

    public function usuarios(): void
    {
        $usuarios = User::all();
        $this->view('admin.usuarios', ['usuarios' => $usuarios]);
    }

    public function guardarUsuario(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $rol = $_POST['rol'] === 'superadmin' ? 'superadmin' : 'paciente';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $nombre === '' || $password === '') {
            $_SESSION['error'] = 'Email válido, nombre y contraseña son obligatorios.';
            $this->redirect('/admin/usuarios');
            return;
        }
        if (User::findByEmail($email)) {
            $_SESSION['error'] = 'Ya existe un usuario con ese correo.';
            $this->redirect('/admin/usuarios');
            return;
        }
        User::create(compact('email', 'password', 'nombre', 'rol'));
        $_SESSION['success'] = 'Usuario registrado.';
        $this->redirect('/admin/usuarios');
    }

    public function actualizarUsuario(string $id): void
    {
        $id = (int) $id;
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $rol = $_POST['rol'] === 'superadmin' ? 'superadmin' : 'paciente';
        $activo = isset($_POST['activo']) ? 1 : 0;
        $password = (string) ($_POST['password'] ?? '');
        User::update($id, compact('nombre', 'rol', 'activo') + ($password !== '' ? ['password' => $password] : []));
        $_SESSION['success'] = 'Usuario actualizado.';
        $this->redirect('/admin/usuarios');
    }

    public function correo(): void
    {
        $pacientes = User::allPacientes();
        $this->view('admin.correo', ['pacientes' => $pacientes]);
    }

    public function enviarCorreo(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $asunto = trim((string) ($_POST['asunto'] ?? ''));
        $mensaje = trim((string) ($_POST['mensaje'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $asunto === '' || $mensaje === '') {
            $_SESSION['error'] = 'Email válido, asunto y mensaje son obligatorios.';
            $this->redirect('/admin/correo');
            return;
        }
        $nombre = trim((string) ($_POST['nombre'] ?? 'Paciente'));
        try {
            $html = '<p>' . nl2br($this->e($mensaje)) . '</p>';
            (new MailService())->send($email, $nombre, $asunto, $html);
            $_SESSION['success'] = 'Correo enviado correctamente.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error al enviar: ' . $e->getMessage();
        }
        $this->redirect('/admin/correo');
    }
}
