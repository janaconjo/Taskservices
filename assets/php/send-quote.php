<?php
// Configurações
$to = "clients@taskservices.co.mz"; // Email da empresa
$dbHost = "localhost";
$dbUser = "SEU_USUARIO";
$dbPass = "SUA_SENHA";
$dbName = "SUA_BASE";

// Receber e decodificar os dados
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
  http_response_code(400);
  echo json_encode(["error" => "Dados inválidos"]);
  exit;
}

$nome = $data['nome'];
$email = $data['email'];
$telefone = $data['telefone'];
$localizacao = $data['localizacao'];
$contactoPreferido = $data['contactoPreferido'];
$servicos = json_encode($data['servicos']);
$pdfBase64 = $data['pdfBase64'];

// Salvar PDF no servidor
$filename = "Cotacao_{$nome}_" . date("Ymd_His") . ".pdf";
$caminhoPDF = "cotacoes/" . $filename;

if (!file_exists("cotacoes")) {
  mkdir("cotacoes", 0777, true);
}
file_put_contents($caminhoPDF, base64_decode($pdfBase64));

// Conectar ao banco de dados e salvar
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(["error" => "Erro na conexão com a base de dados"]);
  exit;
}
$stmt = $conn->prepare("INSERT INTO cotacoes (nome, email, telefone, localizacao, contacto_preferido, servicos, caminho_pdf) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $nome, $email, $telefone, $localizacao, $contactoPreferido, $servicos, $caminhoPDF);
$stmt->execute();
$stmt->close();
$conn->close();

// Enviar e-mail com anexo
$subject = "Nova Cotação - $nome";
$message = "Uma nova cotação foi enviada.\n\nNome: $nome\nEmail: $email\nTelefone: $telefone\nLocalização: $localizacao\nPreferência de contacto: $contactoPreferido";

$boundary = md5(time());
$headers = "From: sistema@taskservices.co.mz\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

$attachment = chunk_split(base64_encode(file_get_contents($caminhoPDF)));

$body = "--$boundary\r\n";
$body .= "Content-Type: text/plain; charset=utf-8\r\n\r\n";
$body .= "$message\r\n";
$body .= "--$boundary\r\n";
$body .= "Content-Type: application/pdf; name=\"$filename\"\r\n";
$body .= "Content-Disposition: attachment; filename=\"$filename\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
$body .= "$attachment\r\n";
$body .= "--$boundary--";

mail($to, $subject, $body, $headers);

echo json_encode(["status" => "sucesso", "arquivo_salvo" => $caminhoPDF]);
