<?php

namespace App\Events;

use App\Models\Paciente;
use App\Models\Profesional;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevoPacienteMatch implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $paciente;
    public $profesional;
    public $compatibilidad;
    public $sintomasDetectados;

    public function __construct(Paciente $paciente, Profesional $profesional, $compatibilidad, $sintomasDetectados)
    {
        $this->paciente = $paciente;
        $this->profesional = $profesional;
        $this->compatibilidad = $compatibilidad;
        $this->sintomasDetectados = $sintomasDetectados;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('profesional.' . $this->profesional->id_profesional);
    }

    public function broadcastAs()
    {
        return 'nuevo.paciente.match';
    }

    public function broadcastWith()
    {
        return [
            'paciente' => [
                'id' => $this->paciente->id_paciente,
                'nombre' => $this->paciente->usuario->nombre,
                'apellido' => $this->paciente->usuario->apellido,
                'edad' => $this->paciente->obtenerEdad(),
                'genero' => $this->paciente->genero
            ],
            'compatibilidad' => $this->compatibilidad,
            'sintomas_detectados' => $this->sintomasDetectados,
            'timestamp' => now()->toISOString()
        ];
    }
}