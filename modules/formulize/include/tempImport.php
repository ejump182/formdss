<html>
    <body>
        <?php
            include "../../../mainfile.php";
            include "syccompare.php";
            /*
             * TO DO:
             *
             * 1. Talk to Andrew about csvToDatabase() function
             * 2. Delete temp folder and its CSV files
             */
            
            echo "calling doImport<br>";
            doImport(XOOPS_ROOT_PATH . "/modules/formulize/export/test.zip");
            echo "returned from doImport<br>";
            /*
             * doImport function imports template files and current Formulize database state from a ".zip" archive
             *
             * param archivePath        String path to archive file. file should have ".zip" extension
             */
            function doImport($archivePath){
                   $tempCSVFolderPath = extractArchiveFolders($archivePath);
                   csvToDB($tempCSVFolderPath); // probably Andrew's code will go in this function
                   // deleteFolder($tempCSVFolderPath); // clean up temp folder and CSV files
            }
            
            /*
             * csvToDB function sends each exported table to synccompare.php compareRecToDB() function
             *
             * param csvFolderPath      String path to folder containing exported CSV files
             */
            function csvToDB($csvFolderPath){
                if (file_exists($csvFolderPath)){
                    // iterate $csvFolderPath directory and import each file
                    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($csvFolderPath)) as $filePath){
                        if ($filePath->isDir()) continue; // skip "." and ".."
                        echo getTableNameCSV($filePath)[0].", ".getNumDataRowsCSV($filePath)."<br>";
                        //printArr(getTableColsCSV($filePath))."<br>";
                        //printArr(getTableColTypesCSV($filePath))."<br>";
                        for ($line = 1; $line <= getNumDataRowsCSV($filePath); $line ++){
                            echo "data: ";
                            printArr(getDataRowCSV($filePath, $line));
                            //echo compareRecToDB(getTableNameCSV($filePath), getDataRowCSV($filePath, 1), getTableColsCSV($filePath), getTableColTypesCSV($filePath));
                        }
                    }
                }else{
                    die("csv folder path does not exist!");
                }
            }
            
            function printArr($arr){
                foreach($arr as $elem){
                    echo $elem.", ";
                }
                echo "<br>";
            }
            
            /*
             * extractArchiveFolders function calls extractFolder for the folders in the archive
             * WILL OVERWRITE EXISTING FILES CURRENTLY
             *
             * param  archivePath       String path to archive file
             * return tempFolderPath    String path to newly created temp folder. Will be used to delete temp folder
             */
            function extractArchiveFolders($archivePath){
                extractFolder($archivePath, "screens", XOOPS_ROOT_PATH . "/modules/formulize/templates/");
                extractFolder($archivePath, "custom_code", XOOPS_ROOT_PATH . "/modules/formulize/");
                
                // create temporary folder to extract CSV files to. will be deleted later
                $tempFolderPath = XOOPS_ROOT_PATH . "/modules/formulize/temp" . date_format(date_create(), '(U)');
                 if (!file_exists($tempFolderPath) and !mkdir($tempFolderPath)){
                     die("Export folder could not be created.");
                 }
                extractFolder($archivePath, "tables", $tempFolderPath);
                
                return $tempFolderPath;
            }
            
            
            
            /*
             * extractFolder function extracts the given folder to the given extract location from a zip file
             *
             * param archivePath        String path to archive file to be extracted from
             * param folderToExtract    String name of folder within archive the is to be extracted
             * param extractToPath      String path to location to be extracted to
             */
            function extractFolder($archivePath, $folderToExtract, $extractToPath){
                $zip = new ZipArchive;
                if ($zip->open($archivePath) !== TRUE) {
                    die ("Could not open archive");
                }
                
                $files = array();
                for($i = 0; $i < $zip->numFiles; $i++) {
                    $entry = $zip->getNameIndex($i);
                     // strpos() to check if the entry name contains the directory we want to extract
                    if (strpos($entry, $folderToExtract."/") !== false) {
                      $files[] = $entry;
                    }
                }
                
                $zip->extractTo($extractToPath, $files); // extract all files/dirs in $files array to the $extractToPath
            }
            
            
            /*
             * getDataRowCSV function parses the given CSV file and returns the desired row of data (1 is the first line, not 0)
             *
             * param filePath       String path to CSV file to parse
             * param line           int line number to return
             * return dataRow       String array containing row of data
             */
            function getDataRowCSV($filePath, $line){
                $dataRow = array();
                $fileHandle = fopen($filePath, 'r');
                
                fgetcsv($fileHandle); // skip table name
                fgetcsv($fileHandle); // skip column names
                fgetcsv($fileHandle); // skip column types
                
                for ($i = 0; $i < $line; $i ++){
                    $dataRow = fgetcsv($fileHandle);
                    if ($i == $line - 1 && !$dataRow){
                        die("invalid line number");
                    }
                }
                
                fclose($fileHandle);
                return $dataRow;
            }
            
            /*
             * getTableNameCSV function parses the given CSV file and returns the name of the table
             *
             * param filePath       String path to CSV file to parse
             * return tableName     String name of table
             */
            function getTableNameCSV($filePath){
                $fileHandle = fopen($filePath, 'r');
                
                $tableName = fgetcsv($fileHandle); // get table name
                
                fclose($fileHandle);
                return $tableName;
            }
            
            /*
             * getTableColsCSV function parses the given CSV file and returns an array of column names
             *
             * param filePath       String path to CSV file to parse
             * return cols          String array of column names
             */
            function getTableColsCSV($filePath){
                $fileHandle = fopen($filePath, 'r');
                
                fgetcsv($fileHandle); // skip table name
                $cols = fgetcsv($fileHandle); // get column names
                
                fclose($fileHandle);
                return $cols;
            }
            
            /*
             * getTableColTypesCSV function parses the given CSV file and returns an array of column types
             *
             * param filePath       String path to CSV file to parse
             * return colTypes      String array of column types
             */
            function getTableColTypesCSV($filePath){
                $fileHandle = fopen($filePath, 'r');
                
                fgetcsv($fileHandle); // skip table name
                fgetcsv($fileHandle); // skip column names
                $colTypes = fgetcsv($fileHandle); // get column types
                
                fclose($fileHandle);
                return $colTypes;
            }
            
            /*
             * getNumDataRowsCSV function parses the given CSV file and returns number of data rows
             *
             * param filePath       String path to CSV file to parse
             * return numRows       int number of data rows in file
             */
            function getNumDataRowsCSV($filePath){
                $numRows = 0;
                $fileHandle = fopen($filePath, 'r');
                
                fgetcsv($fileHandle); // skip table name
                fgetcsv($fileHandle); // skip column names
                fgetcsv($fileHandle); // skip column types
                while(!feof($fileHandle)){ // iterate through file until eof
                    $content = fgets($fileHandle);
                    if($content){ // line is non empty, must be data so increment number of rows
                        $numRows++;
                    }
              }
                
                fclose($fileHandle);
                return $numRows;
            }
        ?>
    </body>
</html>