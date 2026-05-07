<?php
    $exist=false;
    if(isset($_GET['nom'],$_GET['prenom'],$_GET['email'],$_GET['telephone'],$_GET['adresse'],$_GET['code_postal'])){
        $exist=true;
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php if($exist==true){ ?>
        <h1>Informations de contact</h1>
    <table>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Adresse</th>
            <th>Code Postal</th>
        </tr>
        <tr>
            <td><?php echo $_GET['nom']; ?></td>
            <td><?php echo $_GET['prenom']; ?></td>
            <td><?php echo $_GET['email']; ?></td>
            <td><?php echo $_GET['telephone']; ?></td>
            <td><?php echo $_GET['adresse']; ?></td>
            <td><?php echo $_GET['code_postal']; ?></td>
    </table>
    <?php }else{ echo "<h1>Aucune information de contact reçue.</h1>"; } ?>

</body>
</html>