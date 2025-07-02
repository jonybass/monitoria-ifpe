<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'monitor') {
    header("Location: index.php");
    exit;
}

include __DIR__ . '/conexao.php';
require('fpdf.php');

$usuario_monitor = $_SESSION['usuario'];
$id_materia = $_GET['id_materia'] ?? null;
$mes_ano = $_GET['mes_ano'] ?? null;

if (!$id_materia || !$mes_ano) {
    die("Informe o filtro de matéria e mês.");
}

// Busca dados do monitor
$stmt = $conn->prepare("SELECT id FROM monitores WHERE usuario = ?");
$stmt->bind_param("s", $usuario_monitor);
$stmt->execute();
$result = $stmt->get_result();
$monitor = $result->fetch_assoc();
$id_monitor = $monitor['id'];
$stmt->close();

// Busca dados da matéria
$stmt = $conn->prepare("SELECT nome FROM materias WHERE id = ? AND id_monitor = ?");
$stmt->bind_param("ii", $id_materia, $id_monitor);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    die("Matéria inválida ou não pertence ao monitor.");
}
$materia = $result->fetch_assoc();
$stmt->close();

// Define datas do mês
$data_inicio = $mes_ano . '-01';
$data_fim = date("Y-m-t", strtotime($data_inicio));

// Busca alunos inscritos na matéria
$sqlAlunos = "
    SELECT a.id, a.usuario
    FROM alunos a
    JOIN alunos_materias am ON a.id = am.id_aluno
    WHERE am.id_materia = ?
    ORDER BY a.usuario
";
$stmt = $conn->prepare($sqlAlunos);
$stmt->bind_param("i", $id_materia);
$stmt->execute();
$resultAlunos = $stmt->get_result();

$alunos = [];
while ($row = $resultAlunos->fetch_assoc()) {
    $alunos[$row['id']] = $row['usuario'];
}
$stmt->close();

// Busca frequências de todos os alunos para as datas do mês
$sqlFreq = "
    SELECT id_aluno, data, presente
    FROM frequencias_alunos
    WHERE id_materia = ? AND data BETWEEN ? AND ?
";
$stmt = $conn->prepare($sqlFreq);
$stmt->bind_param("iss", $id_materia, $data_inicio, $data_fim);
$stmt->execute();
$resultFreq = $stmt->get_result();

$frequencias = [];
while ($row = $resultFreq->fetch_assoc()) {
    $frequencias[$row['id_aluno']][$row['data']] = $row['presente'];
}
$stmt->close();

// Cria array com todos os dias do mês (formatados d/m)
$periodo = new DatePeriod(
    new DateTime($data_inicio),
    new DateInterval('P1D'),
    (new DateTime($data_fim))->modify('+1 day')
);
$diasMes = [];
foreach ($periodo as $date) {
    $diasMes[] = $date->format('Y-m-d');
}

// Classe PDF customizada com cabeçalho e rodapé
class PDF extends FPDF {
    function Header() {
        // Logo IFPE (coloque o caminho correto do seu logo, se quiser)
        $this->Image('logo_ifpe.png',10,6,30);
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'INSTITUTO FEDERAL DE EDUCACAO, CIENCIA E TECNOLOGIA DE PERNAMBUCO',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,6,'Campus Igarassu - Relatorio Mensal de Frequencia de Monitoria ',0,1,'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-30);
        $this->SetFont('Arial','I',9);
        $this->Cell(0,10,'IFPE - Sistema de Monitoria',0,1,'C');
        $this->Cell(0,5,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF('L','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Relatorio de Frequencia da Monitoria",0,1,'C');
$pdf->Ln(2);

// Dados do monitor, matéria e mês
$pdf->SetFont('Arial','',12);
$pdf->Cell(60,8,"Monitor: ",0,0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,$usuario_monitor,0,1);

$pdf->SetFont('Arial','',12);
$pdf->Cell(60,8,"Materia: ",0,0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,$materia['nome'],0,1);

$pdf->SetFont('Arial','',12);
$pdf->Cell(60,8,"Mes/Ano: ",0,0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,date('m/Y', strtotime($data_inicio)),0,1);

$pdf->Ln(5);

// Cabeçalho da tabela
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(200,200,200);
$pdf->Cell(10,8,'ID',1,0,'C',true);
$pdf->Cell(45,8,'Aluno',1,0,'L',true);

$colWidth = 6; // largura das colunas dos dias
$maxCols = 31; // máximo de dias no mês

foreach ($diasMes as $dia) {
    $pdf->Cell($colWidth,8,date('d', strtotime($dia)),1,0,'C',true);
}

$pdf->Cell(12,8,'Total',1,1,'C',true);

// Dados da tabela
$pdf->SetFont('Arial','',7);

foreach ($alunos as $idAluno => $nomeAluno) {
    $pdf->Cell(10,6,$idAluno,1,0,'C');
    $pdf->Cell(45,6,$nomeAluno,1,0,'L');

    $totalPresencas = 0;
    foreach ($diasMes as $dia) {
        $presente = $frequencias[$idAluno][$dia] ?? 0;
        if ($presente) {
            $pdf->SetTextColor(0,100,0);
            $pdf->Cell($colWidth,6,'P',1,0,'C');
            $totalPresencas++;
        } else {
            $pdf->SetTextColor(150,0,0);
            $pdf->Cell($colWidth,6,'F',1,0,'C');
        }
        $pdf->SetTextColor(0);
    }
    $pdf->Cell(12,6,$totalPresencas,1,1,'C');
}

// Assinaturas
$pdf->Ln(15);
$pdf->Cell(90,8,"_________________________",0,0,'C');
$pdf->Cell(90,8,"_________________________",0,1,'C');

$pdf->Cell(90,5,"Assinatura do Professor",0,0,'C');
$pdf->Cell(90,5,"Assinatura do Monitor",0,1,'C');

$pdf->Output();
exit;
