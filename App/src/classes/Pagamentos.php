<?php 
    namespace Api\App\classes;
    use PDO;

    class pagamentos
    {
        private $lista = [];

        /// busca todos os registros de vendas efetuadas e para quais imoveis foi vendido
        private function buscaDeRegistros(){
            include "../App/Conexao.php";
            $sql = (
                "select 
                    pagamentos.id_venda, 
                    pagamentos.data_do_pagamento, 
                    pagamentos.valor_do_pagamento, 
                    pagamentos.codigo_imovel, 
                    imovel.descricao_imovel, 
                    imovel.tipo_imovel
                from pagamentos
                inner join imovel on pagamentos.codigo_imovel = imovel.codigo_imovel
                "
            );
            $stmt = $conn->prepare($sql);
            if($stmt->execute()){
                $dadosArr =  $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($dadosArr as $dados) {
                    array_push($this->lista, $dados);
                }  
            }
        }
        ///mostra todos os registros de vendas
        public function buscaPagamentos(){
            //executa o metodo privado buscaDeRegistros e retorna/mostra sua versao em json
            $this->buscaDeRegistros();
            $JSONlista = json_encode($this->lista,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            var_dump($JSONlista);
            //limpa o atributo privado $lista 
            $this->lista = [];
        }
        ///mostra um array com a soma de todos os pagamentos mensais dos imoveis
        public function somaValorPorPagamentos(){
            //executa o metodo privado buscaDeRegistros e retorna sua versao em json
            $this->buscaDeRegistros();
            $JSONlista = json_encode($this->lista,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);;
            $arr = json_decode($JSONlista, true);
            /*
            abaixo esta a parte logica(programaçao funcional) para realizar a soma 
            vos valores mensais .
            o array: $listaR tem a funcao de armazenar o id do imovel e usa-lo como chave
            para associar o valor somado de todas os pagamentos feitos pelo id do mesmo imovel
            */
            $listaR = [];
            // esse "for " realiza uma busca no array "arr" para capturar o primeiro id e verificar
            //se ele se repete dentro do array
            for ($i=0; $i < count($arr) ; $i++) { 
                $valor = 0;
                $id = $arr[$i]["codigo_imovel"];
        
                for($j=0; $j <count($arr); $j++) { 
                //apartir desse ponto o comando for acima tem o objetivo de buscar 
                //os valores e com os comando if abaixo realiza uma comparacao.


                // se  o id armazenado pelo primeiro loop for igual ao valor apresentado pelo
                // segundo loop, oseu valor é armazenado na variavel "valor".
                    if($id == $arr[$j]["codigo_imovel"]){
                        $valor += $arr[$j]["valor_do_pagamento"]; 
                    }
                // se nao os valores comparados forem diferentes, esses valores sao armazenados 
                // nas variaveis id e valor
                    if(!$id == $arr[$j]["codigo_imovel"]){
                        $valor = 0;
                        $id = $arr[$j]["codigo_imovel"];
                        $valor = $arr[$j]["valor_do_pagamento"];
                    }
                }

                // apos realizar o loop secundario as variaveis apresentadas sao agrupadas numa 
                //variavel e a mesma é aramazenada no array "lista R"
                $resultado = [$id => $valor];
                array_push($listaR, $resultado). PHP_EOL;
            }

            // apos a realizacao do loop primario é usado o foreach abaixo para criar um novo array 
            // sem a repeticao de chave => valor
            $lista= array();
            foreach ($listaR AS $key => $value) {
                if (!in_array($value, $lista)) {
                    $lista[] = $listaR[$key];
                }
            }
            // retorna o array final

            var_dump(json_encode($lista, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            //limpa o atributo privado $lista
            $this->lista = [];
        }
        ///mostra um array com a soma de todas as vendas feitas no periodo de um mes 
        public function vendasMesAno(){
            //executa o metodo privado buscaDeRegistros e retorna sua versao em json
            $this->buscaDeRegistros();
            $JSONlista = json_encode($this->lista,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $arr =json_decode($JSONlista, true);
            // os codigo abaixo executa o comando array_map para 
            //criar um novo array os valores: data_do_pagamento e valor_do_pagamento
            $listaR = array_map(function($registro){
                //instruçao regex para formatar a data padrao para o formato mes/ano
                $data_pagamento = preg_replace('/(\d{4})-(\d{2})-(\d{2})/', '\2/\1' ,$registro["data_do_pagamento"]);
                $valor = $registro["valor_do_pagamento"];
                $arrG = array(
                    "data_do_pagamento" => $data_pagamento,
                    "valor_do_pagamento" => $valor
                );
                return $arrG;

            }, $arr);

            /* apartir daqui o codigo abaixo tem o objetivo de somar os valore dentro array criado acima e 
               adiciona-los em outro array
            */
            $listaMesAno = [];

            foreach($listaR as $dados){
                $id = $dados["data_do_pagamento"];
                $valor = 0;
                foreach($listaR as $valorAcomparar){
                    if($dados["data_do_pagamento"] == $valorAcomparar["data_do_pagamento"]){
                        $valor += $valorAcomparar["valor_do_pagamento"];
                    }
                    if(!$dados["data_do_pagamento"] == $valorAcomparar["data_do_pagamento"]){
                        $valor = 0;
                        $id = $valorAcomparar["data_do_pagamento"];
                        $valor = $$valorAcomparar["valor_do_pagamento"];
                    }
                } 
                $resultado = [$id => $valor];
                array_push($listaMesAno, $resultado);
            }

            $lista= array();
            // filtra o array criado para nao ter dados repetidos
            foreach ($listaMesAno AS $key => $value) {
                if (!in_array($value, $lista)) {
                    $lista[] = $listaMesAno[$key];
                }
            }

            // mostra o array $lista
            var_dump(json_encode($lista, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            //limpa o atributo privado $lista
            $this->lista = [];
        }
        ///mostra um array com o percentual de vendas efetuadas para determinado tipo de imovel
        public function percentualVendasPorTipoImovel(){
            //executa o metodo privado buscaDeRegistros e retorna sua versao em json
            $this->buscaDeRegistros();
            $JSONlista = json_encode($this->lista,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);;
            $arr = json_decode($JSONlista, true);

            // essa parte cria um array com os tipos de imoveis
            $listaR = array_map(function($registro){
                $tipo_imovel = $registro["tipo_imovel"];
                return strtolower($tipo_imovel);
            }, $arr);
            //realiza uma contagem do array
            $total = count($listaR);


            /*
             o codigo abaixo faz uma contagem de quantas vezes cada tipo de imovel se repete 
             e depois converte o valor para percentual utilizando como 'total' o a variavel ($total).
            */ 
            $lista = array();
            
            foreach($listaR as $tipo){
                $tipo_imovel = $tipo;
                $contador = 0;
                foreach($listaR as $comparacao){
                    if($tipo == $comparacao){
                        $contador++;
                    }
                }
                $porcentagem_vendas = number_format( $contador*100 / $total, 2) ."%" ;
                $resultado = [$tipo_imovel => $porcentagem_vendas];
                array_push($lista, $resultado);
            }

            //filtra o array para nao ter dados repetidos
            $listaPorcentagem= array();

            foreach ($lista AS $key => $value) {
                if (!in_array($value, $listaPorcentagem)) {
                    $listaPorcentagem[] = $lista[$key];
                }
            }

            // mostra o array final
            var_dump( json_encode($listaPorcentagem, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            // limpa o atributo privado $lista
            $this->lista = [];

        }

    }

?>