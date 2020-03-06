# Effect Manager

## Установка через терминал
```bash
cd ./SITE/core/components
git clone https://github.com/der-leksey/modx-effect-manager.git
mv modx-effect-manager emanager && cd emanager
ln -r -s public ../../../public_html/assets/components/emanager
```

## Установка через файловый менеджер
Скачать компонент с github: https://github.com/der-leksey/modx-effect-manager
Распаковать в core/components/emanager
Папку public переименовать и переместить в public_html/assets/components/emanager

**Добавить пространство имён**
emanager
{core_path}components/emanager/
{assets_path}components/emanager/

**Добавить пункт меню**
Ключ словаря: Effect Manager
Действие: index
Пространство имён: emanager