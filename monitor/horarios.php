<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'monitor') {
    header("Location: index.php");
    exit;
}

include '../conexao.php';

$usuario = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT id FROM monitores WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$monitor = $result->fetch_assoc();
$id_monitor = $monitor['id'];

$mensagem = "";

// Busca matérias do monitor logado, com turno
$materias = [];
$stmt = $conn->prepare("SELECT id, nome, turno FROM materias WHERE id_monitor = ?");
$stmt->bind_param("i", $id_monitor);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $materias[] = $row;
}

// Função para validar turno e horário
function validaHorarioPorTurno($turno, $inicio, $fim) {
    $intervalos = [
        'manhã' => ['06:00', '12:00'],
        'tarde' => ['12:00', '18:00'],
        'noite' => ['18:00', '23:59'],
    ];
    if (!isset($intervalos[$turno])) return false;
    list($min, $max) = $intervalos[$turno];
    return ($inicio >= $min && $fim <= $max && $inicio < $fim);
}

// Tratar POST para inserir, atualizar e excluir
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['acao'])) {
        $acao = $_POST['acao'];

        if ($acao === 'inserir') {
            // Inserir novo horário
            $id_materia = $_POST['id_materia'] ?? '';
            $dia = $_POST['dia_semana'] ?? '';
            $inicio = $_POST['horario_inicio'] ?? '';
            $fim = $_POST['horario_fim'] ?? '';

            if ($id_materia && $dia && $inicio && $fim) {
                $turno = null;
                foreach ($materias as $m) {
                    if ($m['id'] == $id_materia) {
                        $turno = strtolower($m['turno']);
                        break;
                    }
                }
                if (!$turno) {
                    $mensagem = "❌ Matéria inválida.";
                } else if (!validaHorarioPorTurno($turno, $inicio, $fim)) {
                    $mensagem = "❌ Horários devem estar dentro do turno '{$turno}' e início menor que fim.";
                } else {
                    $stmt = $conn->prepare("INSERT INTO horarios_monitoria (id_materia, dia_semana, horario_inicio, horario_fim) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $id_materia, $dia, $inicio, $fim);
                    if ($stmt->execute()) {
                        $mensagem = "✅ Horário cadastrado com sucesso!";
                    } else {
                        $mensagem = "❌ Erro ao cadastrar: " . $conn->error;
                    }
                    $stmt->close();
                }
            } else {
                $mensagem = "❗ Todos os campos são obrigatórios.";
            }
        }
        else if ($acao === 'atualizar') {
            // Atualizar horário
            $id_horario = $_POST['id_horario'] ?? '';
            $id_materia = $_POST['id_materia'] ?? '';
            $dia = $_POST['dia_semana'] ?? '';
            $inicio = $_POST['horario_inicio'] ?? '';
            $fim = $_POST['horario_fim'] ?? '';

            if ($id_horario && $id_materia && $dia && $inicio && $fim) {
                $turno = null;
                foreach ($materias as $m) {
                    if ($m['id'] == $id_materia) {
                        $turno = strtolower($m['turno']);
                        break;
                    }
                }
                if (!$turno) {
                    $mensagem = "❌ Matéria inválida.";
                } else if (!validaHorarioPorTurno($turno, $inicio, $fim)) {
                    $mensagem = "❌ Horários devem estar dentro do turno '{$turno}' e início menor que fim.";
                } else {
                    $stmt = $conn->prepare("UPDATE horarios_monitoria SET id_materia=?, dia_semana=?, horario_inicio=?, horario_fim=? WHERE id=?");
                    $stmt->bind_param("isssi", $id_materia, $dia, $inicio, $fim, $id_horario);
                    if ($stmt->execute()) {
                        $mensagem = "✅ Horário atualizado com sucesso!";
                    } else {
                        $mensagem = "❌ Erro ao atualizar: " . $conn->error;
                    }
                    $stmt->close();
                }
            } else {
                $mensagem = "❗ Todos os campos são obrigatórios para atualizar.";
            }
        }
        else if ($acao === 'excluir') {
            // Excluir horário
            $id_horario = $_POST['id_horario'] ?? '';
            if ($id_horario) {
                $stmt = $conn->prepare("DELETE FROM horarios_monitoria WHERE id=?");
                $stmt->bind_param("i", $id_horario);
                if ($stmt->execute()) {
                    $mensagem = "✅ Horário excluído com sucesso!";
                } else {
                    $mensagem = "❌ Erro ao excluir: " . $conn->error;
                }
                $stmt->close();
            } else {
                $mensagem = "❗ ID do horário é obrigatório para exclusão.";
            }
        }
    }
}

// Recarrega os horários cadastrados após ação
$horarios = [];
$sql = "SELECT h.*, m.nome AS nome_materia, m.turno AS turno_materia
        FROM horarios_monitoria h
        JOIN materias m ON h.id_materia = m.id
        WHERE m.id_monitor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_monitor);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $horarios[] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Horários de Monitoria</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .container { max-width: 700px; background: white; padding: 25px; border-radius: 10px; margin: auto; box-shadow: 0 0 10px #ccc; }
        h2 { text-align: center; margin-bottom: 20px; }
        label { font-weight: bold; display: block; margin-top: 15px; }
        input, select { width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; }
        .btn { background-color: #006400; color: white; border: none; padding: 12px; margin-top: 20px; cursor: pointer; width: 100%; border-radius: 6px; font-weight: bold; }
        .btn:hover { background-color: #008000; }
        .mensagem { text-align: center; margin-top: 15px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #006400; color: white; }
        .voltar { text-align: center; margin-top: 20px; }
        .voltar a { text-decoration: none; color: #006400; }
        .info-turno { margin-top: 5px; font-weight: bold; color: #555; }
    </style>
</head>
<body>

<div class="container">
    <h2>Gerenciar Horários</h2>

    <?php if ($mensagem): ?>
        <p class="mensagem"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <form method="POST" id="formHorario">
        <input type="hidden" name="acao" id="acao" value="inserir" />
        <input type="hidden" name="id_horario" id="id_horario" value="" />

        <label for="id_materia">Matéria:</label>
        <select name="id_materia" id="id_materia" required>
            <option value="">Selecione</option>
            <?php foreach ($materias as $m): ?>
                <option value="<?= $m['id'] ?>" data-turno="<?= strtolower($m['turno']) ?>"><?= htmlspecialchars($m['nome']) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="info-turno" id="info_turno" style="display:none;"></div>

        <label for="dia_semana">Dia da Semana:</label>
        <select name="dia_semana" id="dia_semana" required>
            <option value="">Selecione</option>
            <option value="Segunda">Segunda-feira</option>
            <option value="Terça">Terça-feira</option>
            <option value="Quarta">Quarta-feira</option>
            <option value="Quinta">Quinta-feira</option>
            <option value="Sexta">Sexta-feira</option>
            <option value="Sábado">Sábado</option>
        </select>

        <label for="horario_inicio">Horário de Início:</label>
        <input type="time" name="horario_inicio" id="horario_inicio" required disabled>

        <label for="horario_fim">Horário de Término:</label>
        <input type="time" name="horario_fim" id="horario_fim" required disabled>

        <button type="submit" class="btn" id="btn_submit" disabled>Cadastrar Horário</button>
        <button type="button" class="btn" id="btn_cancelar" style="display:none; background:#cc0000; margin-top:10px;">Cancelar Edição</button>
    </form>

    <?php if (count($horarios) > 0): ?>
        <h3 style="margin-top: 40px;">Horários Cadastrados</h3>
        <table>
            <tr>
                <th>Matéria</th>
                <th>Turno</th>
                <th>Dia</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Ações</th>
            </tr>
            <?php foreach ($horarios as $h): ?>
                <tr data-id="<?= $h['id'] ?>" data-id_materia="<?= $h['id_materia'] ?>" data-dia="<?= $h['dia_semana'] ?>" data-inicio="<?= $h['horario_inicio'] ?>" data-fim="<?= $h['horario_fim'] ?>" data-turno="<?= strtolower($h['turno_materia']) ?>">
                    <td><?= htmlspecialchars($h['nome_materia']) ?></td>
                    <td><?= htmlspecialchars($h['turno_materia']) ?></td>
                    <td><?= $h['dia_semana'] ?></td>
                    <td><?= $h['horario_inicio'] ?></td>
                    <td><?= $h['horario_fim'] ?></td>
                    <td>
                        <button class="btn-editar" style="background:#0066cc; color:#fff; border:none; padding:5px 10px; cursor:pointer;">Editar</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Confirma exclusão deste horário?');">
                            <input type="hidden" name="acao" value="excluir" />
                            <input type="hidden" name="id_horario" value="<?= $h['id'] ?>" />
                            <button type="submit" style="background:#cc0000; color:#fff; border:none; padding:5px 10px; cursor:pointer;">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div class="voltar">
        <a href="painel_monitor.php">← Voltar ao Painel</a>
    </div>
</div>

<script>
const intervalos = {
    'manhã': { min: "06:00", max: "12:00" },
    'tarde': { min: "12:00", max: "18:00" },
    'noite': { min: "18:00", max: "23:59" }
};

const selectMateria = document.getElementById('id_materia');
const horarioInicio = document.getElementById('horario_inicio');
const horarioFim = document.getElementById('horario_fim');
const infoTurno = document.getElementById('info_turno');
const btnSubmit = document.getElementById('btn_submit');
const acaoInput = document.getElementById('acao');
const idHorarioInput = document.getElementById('id_horario');
const diaSemana = document.getElementById('dia_semana');
const btnCancelar = document.getElementById('btn_cancelar');
const formHorario = document.getElementById('formHorario');

function habilitarFormulario(turno) {
    if (!turno || !intervalos[turno]) {
        horarioInicio.value = '';
        horarioFim.value = '';
        horarioInicio.disabled = true;
        horarioFim.disabled = true;
        btnSubmit.disabled = true;
        infoTurno.style.display = 'none';
        infoTurno.textContent = '';
        return;
    }
    const intervalo = intervalos[turno];
    horarioInicio.min = intervalo.min;
    horarioInicio.max = intervalo.max;
    horarioFim.min = intervalo.min;
    horarioFim.max = intervalo.max;
    horarioInicio.disabled = false;
    horarioFim.disabled = false;
    btnSubmit.disabled = false;
    infoTurno.style.display = 'block';
    infoTurno.textContent = `Turno: ${turno.charAt(0).toUpperCase() + turno.slice(1)} (${intervalo.min} - ${intervalo.max})`;
}

// Quando mudar matéria, ajustar turno e ativar campos
selectMateria.addEventListener('change', () => {
    const turno = selectMateria.options[selectMateria.selectedIndex]?.dataset.turno;
    habilitarFormulario(turno);
});

// Cancelar edição - limpa o formulário e volta para inserir
btnCancelar.addEventListener('click', () => {
    acaoInput.value = 'inserir';
    idHorarioInput.value = '';
    formHorario.reset();
    horarioInicio.disabled = true;
    horarioFim.disabled = true;
    btnSubmit.disabled = true;
    btnCancelar.style.display = 'none';
    btnSubmit.textContent = 'Cadastrar Horário';
    infoTurno.style.display = 'none';
    infoTurno.textContent = '';
});

// Botões editar
document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        const tr = e.target.closest('tr');
        const id = tr.dataset.id;
        const id_materia = tr.dataset.id_materia;
        const dia = tr.dataset.dia;
        const inicio = tr.dataset.inicio;
        const fim = tr.dataset.fim;
        const turno = tr.dataset.turno;

        // Preencher formulário
        acaoInput.value = 'atualizar';
        idHorarioInput.value = id;
        selectMateria.value = id_materia;
        diaSemana.value = dia;
        habilitarFormulario(turno);
        horarioInicio.value = inicio;
        horarioFim.value = fim;

        btnCancelar.style.display = 'block';
        btnSubmit.textContent = 'Atualizar Horário';
    });
});
</script>

</body>
</html>
