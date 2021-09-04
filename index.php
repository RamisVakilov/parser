    <!-- <style>
        .control-group, .row-fluid, #tab-attachment, .qq-upload-file, .alert, .qq-upload-list{
            display: block;
        }
    </style> -->
    <?php
        include 'simple_html_dom.php';

        ini_set('max_execution_time', 10000); //300 seconds = 5 minutes
        $mysqli = new mysqli('localhost','root','','parser');//создание объекта и подключение к базе mysql
        $mysqli->set_charset('utf8');//выбор кодировки
    
        if(mysqli_connect_errno()){//проверяем подключение к базе данных
            printf('Соединение не установленно', mysqli_connect_error());
            exit('Что то пошло не так');//прекращение работы скрипта
        }
        else{
            echo 'Все хорошо. Процесс занесения в Базу Данных пошел...'.'<br>';
        }
        

        $reg_mail = '#[A-Z0-9._%+-]+@[A-Z0-9-]+.+.[A-Z]{2,4}#im';
        $reg_link = '#\/procedure\/read\/\d* #';
        $reg_short_number = "#\d{4}#";
        $reg_long_number = "#\d{11}#";
       
        $url_site = "https://etp.eltox.ru";

       
        //Получил все страницы пагинации
        for($i=0; $i<194; $i++){
            $pagin_arr="https://etp.eltox.ru/registry/procedure/page/".($i+1);
            $item_pagin_page = file_get_contents($pagin_arr);
            echo ($i+1). ' страница'. "<br>";
                    
                //ищу ссылки на выполнение работ
                    preg_match_all($reg_link,$item_pagin_page,$matches);//$matches - массив ссылок на каждой отдельной странице
                //захожу на каждую страницу выполнения работ в отдельно взятой странице из раздела пагинации
                for($j=0; $j < count($matches[0]); $j++){//их не больше 10
                    
                     //меняю ссылки в js и css, что бы контент нормально отображался и документы динамически подгружались
                       
                        $matches[0][$j] = $url_site . $matches[0][$j];
                        $page = file_get_contents($matches[0][$j]);
                        $a = htmlspecialchars($page);
                        $a = str_replace("/assets/","https://etp.eltox.ru/assets/" ,$a);
                        $item_job_page = html_entity_decode($a);
                        

                    //Забираю номера процедур
                        preg_match($reg_short_number,$item_job_page, $arr_short_number);    //!!!!!!!!!!!!!!!!!!!
                        preg_match($reg_long_number,$item_job_page, $arr_long_number);  //!!!!!!!!!!!!!!!!!
                        //echo $item_job_page;
                        
                        //Использую функции библиотеки simple_html_dom.php
                        $document = str_get_html($item_job_page);
                        $elements = $document -> find('#tab-basic > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(10) > td:nth-child(2)');//по классу заношу их в массив elements, как в JS (Document.getElementsByClassName)
                        //по уму нужно через цикл проверять каждый элемент так в разных функциях индекс искомого элемента может меняться, Например через регулярку
                        //Беру mail
                        preg_match($reg_mail, $item_job_page, $arr_mail);   //!!!!!!!!!!!!!!!!!!!!!
                    //Перехожу в документацию и ищу все документы

                    //заношу все данные в Mysql
                    $s=$matches[0][$j];
                    $email = strip_tags($arr_mail[0]);
                     $query ="INSERT INTO `parser_table` (`page`, `short_procedure`, `long_procedure`, `email`, `document`) 
                            VALUES ('$s', '$arr_short_number[0]', '$arr_long_number[0]', '$email','$elements[19]')";
                     $mysqli->query($query);
                      echo ($j+1) . "-ая процедура на текущей странице" . "<br>";  
                      echo $s.'<br>'; 
                      echo $arr_short_number[0].'<br>'; 
                      echo $arr_long_number[0].'<br>'; 
                      echo $email.'<br>';
                      echo $elements[19].'<br>'.'<br>';//иногда индекс нужен другой
                    //break;
                }
            //break;    
        }
            
       $mysqli->close();//закрываем доступ к базе данных
    ?>
    
