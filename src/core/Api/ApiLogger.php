<?php
namespace Hyla\Api;

class ApiLogger
{
    /**
     * Log API
     */
    const API_PATH = 'var/log/api.log';

    /**
     * @staticvar int Log level
     */
    const LOG_DEBUG = 0;
    const LOG_INFO = 1;
    const LOG_ERROR = 2;
    const LOG_CRIT = 3;

    /**
     * flux direction
     */
    const IN = 'IN';
    const OUT = 'OUT';

    /**
     * @var string file path to use
     */
    private $filePath = '';

    /**
     * @var int log level (DEBUG, INFO, etc)
     */
    private $level;

    /**
     * @var File
     */
    private $file;

    /**
     * @var string flux direction
     */
    private $type;

    /**
     * @var string
     */
    private $object;

    /**
     * @var array list level
     */
    protected $logLevel = array(
        0 => 'DEBUG',
        1 => 'INFO',
        2 => 'ERROR',
        3 => 'CRIT'
    );


    public function __construct()
    {
        $this->filePath = ROOT_DIR . self::API_PATH;
        $this->file = new File($this->getConfig());
    }


    /**
     * Write message
     *
     * @param $object
     * @param $type
     * @param array $datas
     * @param null $optionalDatas
     * @param null $level
     */
    public function log($object, $type, array $datas, $optionalDatas = null, $level = null)
    {
        $this->object = $object;
        $this->type = $type;
        $oldLogLevel = $this->level;
        $this->level =  ($level !== null && !empty($this->logLevel[$level])) ? $level : $this->level;

        $this->write($datas, $optionalDatas);

        $this->level = $oldLogLevel;
    }


    /**
     * Get default config and set file path in fact of log type
     *
     * @return mixed
     */
    private function getConfig()
    {
        $config = Conf::getConfig('logger');
        if ($this->filePath !== null) {
            $config['file'] = $this->filePath;
        }
        $this->level = array_search($config['log_level'], $this->logLevel);
        $this->level = $this->level === false ? null : $this->level;

        return $config;
    }


    /**
     * create message
     *
     * @param $message
     * @return string
     */
    private function createLog($message)
    {
        return date('Y-m-d H:i:s')
                . " [{$this->logLevel[$this->level]}] "
                . " [{$this->object}]  [{$this->type}]  $message "
                . PHP_EOL;
    }


    /**
     * write on file and output
     *
     * @param array $datas
     * @param $optionalDatas
     */
    private function write(array $datas, $optionalDatas)
    {
        $formattedMessage = $this->createMessage($datas, $optionalDatas);
        $formattedLog = $this->createLog($formattedMessage);

        if ($this->file !== null) {
            $this->file->log($formattedLog, $this->level);
        } else {
            $console = new Console();
            $console->output($formattedLog, $this->logLevel[$this->level]);
        }
    }


    /**
     * Create message
     *
     * @param array $datas
     * @param string $optionalDatas
     * @return string
     */
    private function createMessage(array $datas, $optionalDatas = '')
    {
        switch ($this->type) {
            case self::IN :
                $optionInfo = '[Method] : ' . Server::getInstance()->getRequestMethod() . "\n"
                    . "[Input] : $optionalDatas\n";
                break;
            case self::OUT :
                $optionInfo = "[Status] : $optionalDatas\n";
                break;
            default:
                $optionInfo = $optionalDatas;
        }

        $message = "\n"
            . '[IP] : ' . Server::getInstance()->getIP() . "\n"
            . (Server::getInstance()->getLocalIP() !== null ? '[Local IP] : ' . Server::getInstance()->getLocalIP() . "\n" : '')
            . '[URI] : ' . Server::getInstance()->getUri() . "\n"
            . $optionInfo;

        return json_encode($datas, JSON_PRETTY_PRINT) . "\n";
    }
}
