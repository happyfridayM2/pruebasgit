<?php
namespace Magebees\Products\Controller\Adminhtml\Downloadimg;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Uploadimg extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        $files =  $this->getRequest()->getFiles();
        $error = [];
        if (isset($files['filename']['name']) && $files['filename']['name'] != '') {
            try {
                $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA);
                $media_dir = $mediaDirectory->getAbsolutePath('import');
                if (!is_dir($media_dir)) {
                    mkdir($media_dir, 0777);
                }
                $datetime = date('m-d-Y_h-i-s', time());
                $export_file_name = "import_images_".$datetime.".csv";
                $listheaader = ["store","websites","type","sku","image","small_image","thumbnail","gallery"];
            
                $varDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::VAR_DIR);
                $var_dir = $varDirectory->getAbsolutePath('import');
                if (!is_dir($var_dir)) {
                    mkdir($var_dir, 0777);
                }
            
                $headerfiles = fopen($var_dir.'/'.$export_file_name, "w");
                fputcsv($headerfiles, $listheaader);
                fclose($headerfiles);
            
                $file=$files['filename']['tmp_name'];
                $fp=fopen($file, "r");
                $ArrSourse = fgetcsv($fp);

                while ($ArrSourse = fgetcsv($fp)) {
                    $images =  explode("|", $ArrSourse[4]);
                    $mainimg = '';
                    $galleryimg = '';
                    for ($i=0; $i<count($images); $i++) {
                        $URL = urldecode($images[$i]);
                        $image_name = (stristr($URL, '?', true))?stristr($URL, '?', true):$URL;
                        $pos = strrpos($image_name, '/');
                        $image_name = substr($image_name, $pos+1);
                        $image = explode(".", $image_name);
                        $extension = end($image);
                        if (strtolower($extension) == 'jpg' || strtolower($extension) == 'png' || strtolower($extension) == 'gif' || strtolower($extension) == 'jpeg') {
                            $img_list_filename = $media_dir . '/' . $image_name;
                            if (file_exists($img_list_filename)) {
                                $path_parts = pathinfo($image_name);
                                $image_name = $path_parts['filename'].'_'.time().'.'.$path_parts['extension'];
                            }
                                
                            $content = file_get_contents($images[$i]);
                            $fps = fopen($media_dir."/".$image_name, "w");
                            fwrite($fps, $content);
                            fclose($fps);
                            if ($i==0) {
                                $mainimg .= "/".$image_name;
                            } else {
                                if ($i == (count($images)-1)) {
                                    $galleryimg .= "/".$image_name;
                                } else {
                                    $galleryimg .= "/".$image_name."|";
                                }
                            }
                        } else {
                            //Mage::log($ArrSourse[2]. " => ".$images[$i],null,'images.txt');
                        }
                    }
                    $listimg = [$ArrSourse[0],$ArrSourse[1],$ArrSourse[2],$ArrSourse[3],$mainimg,$mainimg,$mainimg,$galleryimg];
                    $files = fopen($var_dir.'/'.$export_file_name, "a");
                    fputcsv($files, $listimg);
                    fclose($files);
                }
            } catch (\Exception $e) {
                $error[] = "<div class='message message-error error'><div data-ui-id='messages-message-error'>".$e->getMessage()."</div></div>";
                return;
            }
            if (!empty($error)) {
                $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($error));
                return;
            }
            $result = "<div class='message message-success success'><div data-ui-id='messages-message-success'>Images Downloaded Successfully and <b>".$export_file_name."</b> CSV file generated successfully in <b>var/import</b> folder</div></div>";
            $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result));
        }
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::downloadimg');
    }
}
