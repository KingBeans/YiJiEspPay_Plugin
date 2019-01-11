# 主体内容

## 插件目录结构

1. 支付插件存储于/modules/checkout目录下，我们创建了一个yijiesppay的目录这个目录定义了一个插件名称，在这个目录下我们定义了一个module.yijiesppay.php的文件，这个文件的格式是按照Interspire Shopping Cart系统的要求定义的，要求是module.xxx.php这里xxx为插件名称和我们定义的哪个目录名一致。
1. 在/modules/checkout/yijiesppay目录下我们还定义了images、lang两个目录image主要存放了本插件使用到的一些图片；lang目录中主要存放了我们的语言支持文件

## 具体步骤

  1. 下载

  ![第1步](https://github.com/manarchliu/YiJiEspPayBy_INC_6.9.1/blob/master/Document/images/inc_1.png)

  1. 解压

  ![第2步](https://github.com/manarchliu/YiJiEspPayBy_INC_6.9.1/blob/master/Document/images/inc_2.png)

  1. 拷贝文件

  ![第3步](https://github.com/manarchliu/YiJiEspPayBy_INC_6.9.1/blob/master/Document/images/inc_3.png)

  1. 粘贴文件 1

  ![第4步](https://github.com/manarchliu/YiJiEspPayBy_INC_6.9.1/blob/master/Document/images/inc_4.png)

  1. 粘贴文件 2

  ![第5步](https://github.com/manarchliu/YiJiEspPayBy_INC_6.9.1/blob/master/Document/images/inc_5.png)

  1. 后台设置 1

  ![第6步](https://github.com/manarchliu/YiJiEspPayBy_INC_6.9.1/blob/master/Document/images/inc_6.png)

  1. 后台设置 2

  ![第7步](https://github.com/manarchliu/YiJiEspPayBy_INC_6.9.1/blob/master/Document/images/inc_7.png)

  1. 保存后台设置

  ![第8步](https://github.com/manarchliu/YiJiEspPayBy_INC_6.9.1/blob/master/Document/images/inc_8.png)


`注：本方式仅提供参考，具体操作更具您的环境操作，但是原理都是一致的。`

## 代码组织

主要代码存放于module.yijiesppay.php文件中我们定义了CHECKOUT_YIJIESPPAY类这个类继承于系统原有的ISC_CHECKOUT_PROVIDER类。关于本类的方法请详细阅读代码中的注释。
