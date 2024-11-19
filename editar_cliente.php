<?php
session_start(); 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit; 
}

include 'db.php';  
include 'header.php';  

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$clienteId = $_GET['id'];
$cliente = $pdo->prepare('SELECT * FROM clientes WHERE id = ?');
$cliente->execute([$clienteId]);
$cliente = $cliente->fetch();

if (!$cliente) {
    header('Location: dashboard.php');
    exit;
}

if (isset($_GET['remove_foto']) && $_GET['remove_foto'] == 'true') {
    if ($cliente['foto_cliente']) {
        $fotoPath = 'uploads/' . $cliente['foto_cliente'];

        if (file_exists($fotoPath)) {
            unlink($fotoPath);
        }

        $stmt = $pdo->prepare('UPDATE clientes SET foto_cliente = NULL WHERE id = ?');
        $stmt->execute([$clienteId]);

        $_SESSION['mensagem'] = "Foto removida com sucesso!";
    }

    echo "<script>
        window.location.href = 'editar_cliente.php?id=" . $clienteId . "';
    </script>";
    exit;
}

if ($_POST) {
    $dadosCliente = [
        'cpf_cnpj' => $_POST['cpf_cnpj'],
        'nome' => $_POST['nome'],
        'rg_ie' => $_POST['rg_ie'] ?? null,
        'email' => $_POST['email'],
        'endereco' => $_POST['endereco'] ?? null,
        'conjugue' => $_POST['conjugue'] ?? null,
        'nome_mae' => $_POST['nome_mae'] ?? null,
        'data_nascimento' => $_POST['data_nascimento'] ?? null,
        'local_nascimento' => $_POST['local_nascimento'] ?? null,
        'pasep_pis' => $_POST['pasep_pis'] ?? null,
        'numero_beneficio' => $_POST['numero_beneficio'] ?? null,
    ];

    if (isset($_FILES['foto_cliente']) && $_FILES['foto_cliente']['error'] == 0) {
        $fotoTmp = $_FILES['foto_cliente']['tmp_name'];
        $fotoNome = time() . '_' . $_FILES['foto_cliente']['name'];
        $fotoDestino = 'uploads/' . $fotoNome;
        move_uploaded_file($fotoTmp, $fotoDestino);
        $dadosCliente['foto_cliente'] = $fotoNome;
    } else {
        $dadosCliente['foto_cliente'] = $cliente['foto_cliente'];
    }

    $_SESSION['mensagem'] = "Dados do cliente atualizados com sucesso.";

    $stmt = $pdo->prepare('UPDATE clientes SET cpf_cnpj = ?, nome = ?, rg_ie = ?, email = ?, endereco = ?, conjugue = ?, nome_mae = ?, data_nascimento = ?, local_nascimento = ?, pasep_pis = ?, numero_beneficio = ?, foto_cliente = ? WHERE id = ?');
    $stmt->execute([
        $dadosCliente['cpf_cnpj'],
        $dadosCliente['nome'],
        $dadosCliente['rg_ie'],
        $dadosCliente['email'],
        $dadosCliente['endereco'],
        $dadosCliente['conjugue'],
        $dadosCliente['nome_mae'],
        $dadosCliente['data_nascimento'],
        $dadosCliente['local_nascimento'],
        $dadosCliente['pasep_pis'],
        $dadosCliente['numero_beneficio'],
        $dadosCliente['foto_cliente'],
        $clienteId
    ]);

    echo "<script>
        window.location.href = 'editar_cliente.php?id=" . $clienteId . "';
    </script>";
    exit;

}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente - Sistema Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border-radius: 10px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
        }
        h2 {
            color: #007bff; 
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3; 
        }
        .alert {
            border-radius: 10px; 
        }
        #loading {
            display: none;
            text-align: center;
            margin: 20px 0;
            color: red;
        }
        #loading img {
            width: 30px;
            height: 30px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card p-4">
        <h2>Editar Cliente: <?php echo htmlspecialchars($cliente['nome']); ?></h2>

        <?php
        if (isset($_SESSION['mensagem'])) {
            echo "<div class='alert alert-info'>" . $_SESSION['mensagem'] . "</div>";
            unset($_SESSION['mensagem']); 
        }
        ?>

        <form method="POST" enctype="multipart/form-data" onsubmit="showLoading()">
            <div id="loading">
                <img src="https://i.gifer.com/YCZH.gif" alt="Carregando..."> Carregando, por favor aguarde...
            </div>
            <div class="mb-3">
                <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
                <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" value="<?php echo htmlspecialchars($cliente['cpf_cnpj']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="rg_ie" class="form-label">RG/IE</label>
                <input type="text" class="form-control" id="rg_ie" name="rg_ie" value="<?php echo htmlspecialchars($cliente['rg_ie']); ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="endereco" class="form-label">Endereço</label>
                <input type="text" class="form-control" id="endereco" name="endereco" value="<?php echo htmlspecialchars($cliente['endereco']); ?>">
            </div>
            <div class="mb-3">
                <label for="conjugue" class="form-label">Cônjuge</label>
                <input type="text" class="form-control" id="conjugue" name="conjugue" value="<?php echo htmlspecialchars($cliente['conjugue']); ?>">
            </div>
            <div class="mb-3">
                <label for="nome_mae" class="form-label">Nome da Mãe</label>
                <input type="text" class="form-control" id="nome_mae" name="nome_mae" value="<?php echo htmlspecialchars($cliente['nome_mae']); ?>">
            </div>
            <div class="mb-3">
                <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($cliente['data_nascimento']); ?>">
            </div>
            <div class="mb-3">
                <label for="local_nascimento" class="form-label">Local de Nascimento</label>
                <input type="text" class="form-control" id="local_nascimento" name="local_nascimento" value="<?php echo htmlspecialchars($cliente['local_nascimento']); ?>">
            </div>
            <div class="mb-3">
                <label for="pasep_pis" class="form-label">PASEP/PIS</label>
                <input type="text" class="form-control" id="pasep_pis" name="pasep_pis" value="<?php echo htmlspecialchars($cliente['pasep_pis']); ?>">
            </div>
            <div class="mb-3">
                <label for="numero_beneficio" class="form-label">Número de Benefício</label>
                <input type="text" class="form-control" id="numero_beneficio" name="numero_beneficio" value="<?php echo htmlspecialchars($cliente['numero_beneficio']); ?>">
            </div>
            <div class="mb-3">
                <label for="foto" class="form-label">Foto</label>
                <?php if ($cliente['foto_cliente']): ?>
                    <div>
                        <img src="uploads/<?php echo htmlspecialchars($cliente['foto_cliente']); ?>" alt="Foto do cliente" width="100" height="100">
                        <a href="editar_cliente.php?id=<?php echo $clienteId; ?>&remove_foto=true" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash-alt"></i> Remover Foto
                        </a>

                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="foto_cliente" name="foto_cliente" accept="image/*">
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="detalhes_cliente.php?id=<?php echo urlencode($cliente['id']); ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showLoading() {
    document.getElementById('loading').style.display = 'block'; 
}
</script>
</body>
</html>
<?php include 'footer.php'; ?>
