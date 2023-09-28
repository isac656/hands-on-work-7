<?php
require "vendor/autoload.php";

use Api\App\classes\pagamentos;

$teste = new pagamentos(); 

$CAMINHO = $_SERVER["REQUEST_URI"];
   if(str_contains($CAMINHO, "/vendasMesAno")){
      $teste->vendasMesAno();
   }else
   if(str_contains($CAMINHO, "/buscaPagamentos")){
      $teste->buscaPagamentos();
   }else
   if(str_contains($CAMINHO, "/somaValorPorPagamentos")){
      $teste->somaValorPorPagamentos(); 
   }else
   if(str_contains($CAMINHO, "/percentualVendasPorTipoImovel")){
      $teste->percentualVendasPorTipoImovel();
   }else{
      echo"caminho não encontrado </br>";
      echo "
      digite na url um desses valores após http://localhost:8000 </br>
      </br>
      /buscaPagamentos </br>
      /somaValorPorPagamentos </br>
      /percentualVendasPorTipoImovel </br>
      /vendasMesAno
      ";
   }

?>

