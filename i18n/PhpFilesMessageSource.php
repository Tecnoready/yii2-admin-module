<?php

/*
 * This file is part of the BtoB4Rewards package.
 * 
 * (c) www.btob4rewards.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace asdfstudio\admin\i18n;

use Yii;

/**
 * Cargador de multiples archivos de traduccion de una categoria
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class PhpFilesMessageSource extends \yii\i18n\PhpMessageSource
{
    public $appPath = '@app/messages';
    private $currentPath;

    protected function loadMessages($category, $language) {
        $paths = [$this->basePath, $this->appPath];
        $messages = [];
        foreach ($paths as $path) {
            $this->currentPath = $path;
            $messagesFound = parent::loadMessages($category, $language);
            if(is_array($messagesFound)){
                $messages = array_merge($messages,$messagesFound);
            }
        }
        return $messages;
    }
    
    /**
     * Returns message file path for the specified language and category.
     *
     * @param string $category the message category
     * @param string $language the target language
     * @return string path to message file
     */
    protected function getMessageFilePath($category, $language)
    {
        $messageFile = Yii::getAlias($this->currentPath) . "/$language/";
        
        if (isset($this->fileMap[$category])) {
            $messageFile .= $this->fileMap[$category];
        } else {
            $messageFile .= str_replace('\\', '/', $category) . '.php';
        }

        return $messageFile;
    }
}
