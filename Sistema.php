<?php
  // Incluindo a configuração do banco de dados
  include_once('config.php');

  // Verifica se o formulário foi enviado via POST
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica se todos os campos obrigatórios foram preenchidos
    if (!empty($_POST['nome']) && !empty($_POST['data_evento']) && !empty($_POST['localizacao']) && !empty($_POST['descricao']) && !empty($_POST['capacidade'])) {
        // Recebe os dados do formulário
        $nome = $_POST['nome'];
        $data_evento = $_POST['data_evento'];
        $localizacao = $_POST['localizacao'];
        $descricao = $_POST['descricao'];
        $capacidade = $_POST['capacidade'];

        // Sanitizando os dados para evitar SQL Injection
        $nome = mysqli_real_escape_string($conexao, $nome);
        $data_evento = mysqli_real_escape_string($conexao, $data_evento);
        $localizacao = mysqli_real_escape_string($conexao, $localizacao);
        $descricao = mysqli_real_escape_string($conexao, $descricao);
        $capacidade = mysqli_real_escape_string($conexao, $capacidade);

        // Insere os dados no banco de dados
        $query = "INSERT INTO usuarios (nome_evento, data_evento, local_evento, descricao, capacidade, capacidade_atual) 
                  VALUES ('$nome', '$data_evento', '$localizacao', '$descricao', '$capacidade', 0)";

        // Executa a consulta
        $result = mysqli_query($conexao, $query);

        // Verifica se a inserção foi bem-sucedida
        if ($result) {
            $message = "Evento criado com sucesso!";
        } else {
            $message = "Erro ao criar evento: " . mysqli_error($conexao);
        }
    } else {
        $message = "Todos os campos são obrigatórios!";
    }
  }

  // Consulta para listar os eventos
  $query_list = "SELECT * FROM usuarios";
  $result_list = mysqli_query($conexao, $query_list);

  // Excluir evento
  if (isset($_POST['delete_event_id'])) {
    $event_id = $_POST['delete_event_id'];

    // Consulta para excluir o evento
    $delete_query = "DELETE FROM usuarios WHERE id = $event_id";
    $delete_result = mysqli_query($conexao, $delete_query);

    if ($delete_result) {
      header('Location: sistema.php');  // Recarrega a página para refletir a exclusão
      exit;
    } else {
      $message = "Erro ao deletar evento: " . mysqli_error($conexao);
    }
  }

  // Entrar no evento (Removido incremento de inscritos)
  if (isset($_POST['enter_event_id'])) {
    $event_id = $_POST['enter_event_id'];

    // Verificar se a capacidade atual já atingiu o limite
    $check_query = "SELECT capacidade FROM usuarios WHERE id = $event_id";
    $check_result = mysqli_query($conexao, $check_query);

    if ($check_result) {
      $evento = mysqli_fetch_assoc($check_result);

      if ($evento['capacidade'] > 0) {
        echo "Inscrição realizada com sucesso!";
      } else {
        echo "Capacidade cheia!";
      }
    } else {
      $message = "Erro ao consultar evento: " . mysqli_error($conexao);
    }
  }

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <title>Sistema de Gerenciamento de Eventos</title>
  <style>
    :root {
      --primary: #2c3e50;
      --secondary: #3498db;
      --success: #27ae60;
      --danger: #e74c3c;
      --light: #ecf0f1;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background-color: var(--light);
      font-size: 16px;
    }

    .navbar {
      background-color: var(--primary);
      padding: 1rem;
      color: white;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }

    .card {
      background: white;
      border-radius: 8px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .form-group {
      margin-bottom: 1rem;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: bold;
    }

    input, textarea, select {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      margin-bottom: 0.5rem;
    }

    button.btn {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      transition: opacity 0.2s;
    }

    .btn:hover {
      opacity: 0.9;
    }

    .btn-primary {
      background-color: var(--secondary);
      color: white;
    }

    .btn-success {
      background-color: var(--success);
      color: white;
    }

    .btn-danger {
      background-color: var(--danger);
      color: white;
    }

    .event-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1rem;
      margin-top: 2rem;
    }

    .event-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 1rem;
      background: white;
    }

    .tabs {
      display: flex;
      margin-bottom: 1rem;
    }

    .tab {
      padding: 0.5rem 1rem;
      cursor: pointer;
      border-bottom: 2px solid transparent;
    }

    .tab.active {
      border-bottom: 2px solid var(--secondary);
      color: var(--secondary);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .animate-in {
      animation: fadeIn 0.3s ease-out;
    }
    #listEventsSection {
      display: none; /* Inicialmente oculta */
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="container">
      <h1>Sistema de Gerenciamento de Eventos</h1>
    </div>
  </nav>

  <div class="container">
    <!-- Exibe a mensagem de sucesso ou erro -->
    <?php if (isset($message)): ?>
      <div class="alert">
        <p><?php echo $message; ?></p>
      </div>
    <?php endif; ?>

    <div class="tabs">
      <div class="tab active" onclick="switchTab('create')">Criar Evento</div>
      <div class="tab" onclick="switchTab('list')">Listar Eventos</div>
    </div>

    <!-- Seção Criar Evento -->
    <div id="createEventSection" class="card animate-in">
      <h2>Criar Novo Evento</h2>
      <form action="sistema.php" method="POST" id="eventForm">
        <div class="form-group">
          <label for="eventName">Nome do Evento</label>
          <input name="nome" type="text" id="eventName" required>
        </div>

        <div class="form-group">
          <label for="eventDate">Data</label>
          <input name="data_evento" type="datetime-local" id="eventDate" required>
        </div>

        <div class="form-group">
          <label for="eventLocation">Local</label>
          <input name="localizacao" type="text" id="eventLocation" required>
        </div>

        <div class="form-group">
          <label for="eventDescription">Descrição</label>
          <textarea name="descricao" id="eventDescription" rows="3" required></textarea>
        </div>

        <div class="form-group">
          <label for="eventCapacity">Capacidade</label>
          <input name="capacidade" type="number" id="eventCapacity" required>
        </div>

        <button type="submit" class="btn btn-primary">Criar Evento</button>
      </form>
    </div>

    <!-- Seção Listar Eventos -->
    <div id="listEventsSection" class="card animate-in">
      <h2>Eventos Existentes</h2>

      <div class="event-list">
  <?php while ($evento = mysqli_fetch_assoc($result_list)): ?>
    <div class="event-card">
      <h3><?php echo $evento['nome_evento']; ?></h3>
      <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($evento['data_evento'])); ?></p>
      <p><strong>Local:</strong> <?php echo $evento['local_evento']; ?></p>
      <p><strong>Descrição:</strong> <?php echo $evento['descricao']; ?></p>
      <p><strong>Capacidade:</strong> <?php echo $evento['capacidade']; ?></p> <!-- Aqui adicionamos a exibição da capacidade -->
      <form action="sistema.php" method="POST">
        <input type="hidden" name="delete_event_id" value="<?php echo $evento['id']; ?>">
        <button type="submit" class="btn btn-danger">Deletar</button>
      </form>
    </div>
  <?php endwhile; ?>
</div>

  <script>
    function switchTab(tab) {
      if (tab === 'create') {
        document.getElementById('createEventSection').style.display = 'block';
        document.getElementById('listEventsSection').style.display = 'none';
      } else {
        document.getElementById('createEventSection').style.display = 'none';
        document.getElementById('listEventsSection').style.display = 'block';
      }
    }

    // Chama a função para exibir a primeira seção (criando eventos por padrão)
    switchTab('create');
  </script>
</body>
</html>
