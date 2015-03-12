# 分片文件下载
该脚本用于分片下载文件

# Usage
```
php patchDownload.php url [output_filename] [chunck_size(unit:M)]
```

例如：
```
php patchDownload.php 'http://opensource.changes.com.cn/sqlmap.tar.gz' sqlmap.tar.gz 2
```