```
此项目引用自同事，只做例行维护
```

# 说明

本项目全为插件项目，主要帮助使用开源电商系统的站长快速集成通过易极付的收款能力。

# 支持的系统

## 境内渠道

| 系统名称 | 版本  | 预授权操作 | 退款操作 | 收银台语言选择 | 下载地址 |
|---|---|---|---|---|---|
|interspire shopping cart|6.9.1|不支持|不支持|支持| [点击](https://github.com/manarchliu/YiJiEspPayBy_INC_6.9.1/archive/master.zip) |
|Wordpress by WooCommerce|..|不支持|支持|支持| [点击](https://github.com/manarchliu/WooCommerce_YiJiEspPay/archive/master.zip) |
|Magento|1.9.2|支持|不支持|支持| [点击]() |
|Zencart|1.5.4|不支持|不支持|支持| [点击]() |

## 境外渠道

| 系统名称 | 版本  | 预授权操作 | 退款操作 | 收银台语言选择 | 下载地址 |
|---|---|---|---|---|---|
|interspire shopping cart|6.9.1|支持|支持|支持| [点击](https://github.com/manarchliu/YiJiEspPayBy_INC_6.9.1/archive/master.zip) |
|Wordpress by WooCommerce|..|不支持|支持|支持| [点击](https://github.com/manarchliu/WooCommerce_YiJiEspPay/archive/master.zip) |
|Magento|1.9.2|支持|不支持|支持| [点击]() |
|Zencart|1.5.4|不支持|不支持|支持| [点击]() |

# QA

Q1：为什么有的系统支持一些操作一些系统不支持？

A：由于我方插件主要面向原生系统开发，有的系统开发的接口比较多所以我方开发插件的时候可以支持的操作也比较多。有的系统开放的接口权限相对较低如果要增加操作需要修改系统源码，但大多数商户都会进行二次开发，若我方修改了系统源码会和商户修改的代码起冲突严重会导致系统崩溃。所以我方开发插件是会根据各个系统的开放程度进行开发。

Q2：如果我所使用的插件没有预授权、退款等操作，但我又需要使用本功能该怎么办？

A：你可以到我方提供的商户后台管理页面进行相关操作，然后到你自己网站后台进行对应状态的修改。
