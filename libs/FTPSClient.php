<?php


class FTPFile
{
    public $name;
    public $type;
    public $size;
    /* @var DateTime */
    public $modified;

    public function __construct($fileArr)
    {
        foreach ($fileArr as $key => $value){
            $lowerKey = strtolower($key);
            if(property_exists($this, $lowerKey)){
                $this->{$lowerKey} = $value;
            }
        }
    }

    public function isDir(): bool {
        return $this->type == "dir";
    }

    public function isFile() : bool {
        return $this->type == "file";
    }
}


class FTPSClient
{
    private $conn;

    /**
     * @throws Exception
     */
    public function __construct($host, $port, $username, $password, $timeout = 30)
    {
        $this->conn = ftp_ssl_connect($host, $port, $timeout);
        if($this->conn === false){
            throw new Exception("error connecting to host: $host");
        }

        $result = ftp_login($this->conn, $username, $password);
        if($result === false){
            throw new Exception("login error using user: $username");
        }

        ftp_set_option($this->conn, FTP_USEPASVADDRESS, false);

        $result = ftp_pasv($this->conn, true);
        if($result === false){
            throw new Exception("error enabling passive mode");
        }
    }

    function UploadFile($remote_file, $remote_data): bool
    {
        $tempHandle = fopen('php://temp', 'w+');
        $wrote = fwrite($tempHandle, $remote_data);
        if($wrote != strlen($remote_data)){
            return false;
        }

        //seek beginning of file pointer
        rewind($tempHandle);

        return ftp_fput($this->conn, $remote_file, $tempHandle, FTP_BINARY);
    }

    function GetFile($remote_file)
    {
        $tempHandle = fopen('php://temp', 'r+');
        $result = ftp_fget($this->conn, $tempHandle, $remote_file, FTP_BINARY);
        if (!$result) {
            return false;
        }

        //seek beginning of file pointer
        rewind($tempHandle);

        return stream_get_contents($tempHandle);
    }

    function DeleteFile($remote_file): bool
    {
        return ftp_delete($this->conn, $remote_file);
    }

    function CreateDirectory($directory): bool
    {
        $result = ftp_mkdir($this->conn, $directory);
        return $result !== false;
    }

    function DeleteDirectory($directory): bool
    {
        return ftp_rmdir($this->conn, $directory);
    }

    /**
     * @return FTPFile[]
     */
    function GetDirectoryList($directory): ?array
    {
        $listResult = ftp_rawlist($this->conn, $directory);
        if($listResult === false){
            return false;
        }

        $files = [];
        foreach ($listResult as $fileRow) {
            $parsed = $this->parseFileListRow($fileRow);
            $files[] = new FTPFile($parsed);
        }
        return $files;
    }

    function Close(): bool
    {
        return ftp_close($this->conn);
    }

    /*
     * @param string $row
     * @return array
     *     An assoc with the following properties:
     *     - name
     *     - type: one of directory, file, link, unknown
     *     - size
     *     - owner
     *     - group
     *     - mask: file permission mask as an octal string
     *     - modified: DateTime object
     */
    private function parseFileListRow($row): array
    {
        preg_match('/^(.)(.{9})\s+\S+\s+(\S+)\s+(\S+)\s+(\S+)\s+(.{12})\s(.+)$/', $row, $matches);

        // The date can be in 2 formats. Month-date hour:minute or Month-date year.
        // The dates should always be in the past, but PHP does not do that by
        // default for relative dates (without year), so subtract a year if
        // necessary.

        $date = new DateTime($matches[6], new DateTimeZone('UTC'));
        if (preg_match('/\d\d:\d\d$/', $matches[6]) && $date > new DateTime()) {
            $date->modify('-1 year');
        }

        return [
            'name' => $matches[7],
            'type' => [
                    'd' => 'directory',
                    'l' => 'link',
                    '-' => 'file',
                ][$matches[1]] ?? 'unknown',
            'size' => $matches[5],
            'owner' => $matches[3],
            'group' => $matches[4],
            'mask' => base_convert(strtr($matches[2], 'rwx-', '1110'), 2, 8),
            'modified' => $date,
        ];
    }

}
