<?php
include "conexao.php";
if(isset($_POST['produtos'])){
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $imagem_url = "";

    // Upload da imagem para o Cloudinary
    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0){
        $cfile = new CURLFile($_FILES['imagem']['tmp_name'], $_FILES['imagem']['type'], $_FILES['imagem']['name']);

        $timestamp = time();
        $string_to_sign = "timestamp=$timestamp$api_secret";
        $signature = sha1($string_to_sign);

        $data = [
            'file' => $cfile,
            'timestamp' => $timestamp,
            'api_key' => $api_key,
            'signature' => $signature
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/upload");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if($response === false){ die("Erro no cURL: " . curl_error($ch)); }
        curl_close($ch);

        $result = json_decode($response, true);
        if(isset($result['secure_url'])){
            $imagem_url = $result['secure_url'];
        } else {
            die("Erro no upload: " . print_r($result, true));
        }  
          }

          if($imagem_url != ""){
            $sql = "INSERT INTO produtos (nome, descricao, preco, imagem_url) VALUES ('$nome', '$descricao', $preco, '$imagem_url')";
            mysqli_query($conexao, $sql) or die("Erro ao inserir: " . mysqli_error($conexao));
        }

        header("Location: index.php");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8"/>
<title>Mural de Produtos</title>
<link rel="stylesheet" href="style.css"/>
</head>
<body>
<div id="main">
    <div id="geral">
        <div id="header">
            <h1>Mural de Produtos</h1>
        </div>