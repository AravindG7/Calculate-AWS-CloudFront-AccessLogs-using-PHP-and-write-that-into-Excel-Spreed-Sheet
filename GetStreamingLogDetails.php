<?php
/**
 * @method class GetStreamingLogDetails 
 * @desc Store Video/Audio Related activity streaming logs information.
 * @author aravindgithub@gmail.com
 * @modifier aravindgithub@gmail.com
 * @return array,Excel xls. document and, JSOn object information.
 */
include_once($_SERVER['DOCUMENT_ROOT'].'/protected/vendor/PHPExcel/PHPExcel.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/protected/vendor/PHPExcel/PHPExcel/IOFactory.php');
require 's3bucket/aws-autoloader.php';
use Aws\S3\S3Client;
use Aws\Ses\SesClient;
use Aws\CloudFront\CloudFrontClient;
use Aws\S3\Exception\S3Exception;

class GetStreamingLogDetails extends AppComponent {
    /*
     *@author :   Aravind 
     *@email  :   aravind@muvi.com
     *@reason :   Audio data for playing :  
     *@functionality : actionGetStreamingLogDetails
     *@date   :   08-08-2018
     *@purpose : get stream content log information
     */
    public function actiongetStreamingLogDetails(){
        try{
            //echo "<pre>";
            $cnt = 1 ;
            $row_cnt = 2;
            $bf_cnt = 0;
            $mysql_insert_bitval = 0;
            // ----------------------------------------------------------------------- Bucket Information capsul.
            $bucketInfo = Array (
                                'localSystemPath'=>$_SERVER['DOCUMENT_ROOT'].'/progress/stream_logs_file',
                                'videoRemoteUrl' => '',
                                'logFileDirectoryName'=>'###################',,    
                                'unsignedFolderPath' => '',
                                'unsignedFolderPathForVideo' => '',
                                'bucket_name' => 'streaming-access-logs-#########',
                                'access_key' => '###################',
                                'secret_key' => '####################',
                                'region_code' => 'us-east-1',
                                'region_name' => 'US',
                                's3url' => 's3.amazonaws.com',
                                's3cmd_file_name' => 's3cfg',
                                'cloudfront_url' => '',
                                'unsigned_cloudfront_url' => ''
                                );
            // ----------------------------------------------------------------------- Get access AWS S3 client object capsul.
            $s3url = S3Client::factory(array(
                'key' => $bucketInfo['access_key'],
                'secret' => $bucketInfo['secret_key'],
                'region' => $bucketInfo['region_code']
            ));
            
            // ----------------------------------------------------------------------- Get List of objects at one short.
            $Bucketobjects = $s3url->getIterator('ListObjects', array(
                    "Bucket" => $bucketInfo['bucket_name'],
                    "Prefix" => "cf-ssa-ndrm-logs-information/"
            )); 
            
            $bucket = $bucketInfo['bucket_name'];
            $directory = 'cf-ssa-ndrm-logs-information';
            $basePath = $_SERVER['DOCUMENT_ROOT'].'/progress/stream_logs_file';
            $baseDirectoryFileInfoPath = $bucketInfo['localSystemPath'].'/cf-ssa-ndrm-logs-information';
            //$s3url->downloadBucket($bucketInfo['localSystemPath'], $bucketInfo['bucket_name'], $bucketInfo['logFileDirectoryName']);
            
            //Total fiels in the directory
            $getFilesFromLocalDir = scandir($baseDirectoryFileInfoPath);
            array_splice($getFilesFromLocalDir, 0, 1);
            array_splice($getFilesFromLocalDir, 0, 1);
            
            if((int)count($getFilesFromLocalDir)>0){
                // ----------------------------------------------------------------------- List all bucket objects information
                //Excel Sheet object information
                $myfile = fopen($bucketInfo['localSystemPath']."/streaming_log_information.xls", "a") or die("Unable to open file!");
                $path = file_exists($bucketInfo['localSystemPath']."/streaming_log_information.xls") ? $bucketInfo['localSystemPath']."/streaming_log_information.xls" :fopen($bucketInfo['localSystemPath']."/streaming_log_information.xls", "a") or die("Unable to open file!"); 
                $objPHPExcel = PHPExcel_IOFactory::load($path);
                
                //Excel sheet log Header Information.
                $buffer_log_heads_string = 'date,time,x-edge-location,sc-bytes,c-ip,cs-method,cs(Host),cs-uri-stem,sc-status,cs(Referer),cs(User-Agent),cs-uri-query,cs(Cookie),x-edge-result-type,x-edge-request-id,x-host-header,cs-protocol,cs-bytes,time-taken,x-forwarded-for,ssl-protocol,ssl-cipher,x-edge-response-result-type,cs-protocol-version,fle-status,fle-encrypted-fields';
                $buffer_log_heads_string = explode(",",$buffer_log_heads_string);
                
                //write log data into excel sheet.
                $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A'.$cnt, $buffer_log_heads_string[0])
                            ->setCellValue('B'.$cnt, $buffer_log_heads_string[1])
                            ->setCellValue('C'.$cnt, $buffer_log_heads_string[2])
                            ->setCellValue('D'.$cnt, $buffer_log_heads_string[3])
                            ->setCellValue('E'.$cnt, $buffer_log_heads_string[4])
                            ->setCellValue('F'.$cnt, $buffer_log_heads_string[5])
                            ->setCellValue('G'.$cnt, $buffer_log_heads_string[6])
                            ->setCellValue('H'.$cnt, $buffer_log_heads_string[7])
                            ->setCellValue('I'.$cnt, $buffer_log_heads_string[8])
                            ->setCellValue('J'.$cnt, $buffer_log_heads_string[9])
                            ->setCellValue('K'.$cnt, $buffer_log_heads_string[10])
                            ->setCellValue('L'.$cnt, $buffer_log_heads_string[11])
                            ->setCellValue('M'.$cnt, $buffer_log_heads_string[12])
                            ->setCellValue('N'.$cnt, $buffer_log_heads_string[13])
                            ->setCellValue('O'.$cnt, $buffer_log_heads_string[14])
                            ->setCellValue('P'.$cnt, $buffer_log_heads_string[15])
                            ->setCellValue('Q'.$cnt, $buffer_log_heads_string[16])
                            ->setCellValue('R'.$cnt, $buffer_log_heads_string[17])
                            ->setCellValue('S'.$cnt, $buffer_log_heads_string[18])
                            ->setCellValue('T'.$cnt, $buffer_log_heads_string[19])
                            ->setCellValue('U'.$cnt, $buffer_log_heads_string[20])
                            ->setCellValue('V'.$cnt, $buffer_log_heads_string[21])
                            ->setCellValue('W'.$cnt, $buffer_log_heads_string[22])
                            ->setCellValue('X'.$cnt, $buffer_log_heads_string[23])
                            ->setCellValue('Y'.$cnt, $buffer_log_heads_string[24])
                            ->setCellValue('Z'.$cnt, $buffer_log_heads_string[25]);
                foreach($Bucketobjects as $object){
                    try{
                            //Key LastModified ETag Size StorageClass Owner ID DisplayName 
                            $live_object = explode('/',$object['Key']);
                            if($live_object[1]==$getFilesFromLocalDir[$bf_cnt]){
                                $readBufferGipFilezh = gzopen($baseDirectoryFileInfoPath.'/'.$getFilesFromLocalDir[$bf_cnt],'r') or die("can't open: $php_errormsg");
                                    while($streamingLogDetailOfInformation = gzgets($readBufferGipFilezh,5000)){
                                        $streamingLogDetailOfInformation = explode(" ",$streamingLogDetailOfInformation);
                                        //log information consist array length is alwas one(1)
                                        // $line is the next line of uncompressed data, up to 5000 bytes 
                                        if(count($streamingLogDetailOfInformation)==1){
                                            $buffer_log_streamingLogDetailOfInformation = explode("\t", $streamingLogDetailOfInformation[0]);
                                            $buffer_log_streamingLogDetailOfInformation = array_combine($buffer_log_heads_string, $buffer_log_streamingLogDetailOfInformation);
                                            //$mysql_buffer_log_insert_information = implode(",",$buffer_log_streamingLogDetailOfInformation);
                                            //echo $mysql_buffer_log_insert_information;exit;
                                            //print_r($buffer_log_streamingLogDetailOfInformation);exit;
                                            if($buffer_log_streamingLogDetailOfInformation['time']!="Error"){
                                                $objPHPExcel->setActiveSheetIndex(0)
                                                    ->setCellValue('A'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['date'])
                                                    ->setCellValue('B'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['time'])
                                                    ->setCellValue('C'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['x-edge-location'])
                                                    ->setCellValue('D'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['sc-bytes'])
                                                    ->setCellValue('E'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['c-ip'])
                                                    ->setCellValue('F'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['cs-method'])
                                                    ->setCellValue('G'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['cs(Host)'])
                                                    ->setCellValue('H'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['cs-uri-stem'])
                                                    ->setCellValue('I'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['sc-status'])
                                                    ->setCellValue('J'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['cs(Referer)'])
                                                    ->setCellValue('K'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['cs(User-Agent)'])
                                                    ->setCellValue('L'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['cs-uri-query'])
                                                    ->setCellValue('M'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['cs(Cookie)'])
                                                    ->setCellValue('N'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['x-edge-result-type'])
                                                    ->setCellValue('O'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['x-edge-request-id'])
                                                    ->setCellValue('P'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['x-host-header'])
                                                    ->setCellValue('Q'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['cs-protocol'])
                                                    ->setCellValue('R'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['cs-bytes'])
                                                    ->setCellValue('S'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['time-taken'])
                                                    ->setCellValue('T'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['x-forwarded-for'])
                                                    ->setCellValue('U'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['ssl-protocol'])
                                                    ->setCellValue('V'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['ssl-cipher'])
                                                    ->setCellValue('W'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['x-edge-response-result-type'])
                                                    ->setCellValue('X'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['cs-protocol-version'])
                                                    ->setCellValue('Y'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['fle-status'])
                                                    ->setCellValue('Z'.$row_cnt, $buffer_log_streamingLogDetailOfInformation['fle-encrypted-fields']);
                                                }
                                         }
                                    }
                            gzclose($readBufferGipFilezh) or die("can't close: $php_errormsg");
                            }
                            $row_cnt+=1;
                            $bf_cnt+=1;
                    }catch(Exception $php_errormsg){
                        echo $e->getMessage() . PHP_EOL;
                    }
                }//Foreach end here.
                // Rename worksheet
                $objPHPExcel->getActiveSheet()->setTitle('streaming_log_information');
                
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $objPHPExcel->setActiveSheetIndex(0);
                
                // Redirect output to a client's web browser (Excel2007)
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="streaming_log_information.xls"');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                ob_clean();
                $objWriter->save('php://output');
                exit();
                //read data from excel sheet.
//                $objPHPExcel = PHPExcel_IOFactory::load($path);
//                $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
//                print_r($allDataInSheet);exit;
            }else{
                echo "Cause some downloading failed...! Please ask for assistance muvi engineering support.";
            }
        } catch (S3Exception $e){
            echo $e->getMessage() . PHP_EOL;
        }   
    }
}
