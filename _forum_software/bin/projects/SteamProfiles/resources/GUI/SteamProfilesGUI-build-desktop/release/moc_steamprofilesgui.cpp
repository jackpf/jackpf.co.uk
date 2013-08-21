/****************************************************************************
** Meta object code from reading C++ file 'steamprofilesgui.h'
**
** Created: Wed 19. Jan 20:43:03 2011
**      by: The Qt Meta Object Compiler version 62 (Qt 4.7.0)
**
** WARNING! All changes made in this file will be lost!
*****************************************************************************/

#include "../../SteamProfilesGUI/steamprofilesgui.h"
#if !defined(Q_MOC_OUTPUT_REVISION)
#error "The header file 'steamprofilesgui.h' doesn't include <QObject>."
#elif Q_MOC_OUTPUT_REVISION != 62
#error "This file was generated using the moc from 4.7.0. It"
#error "cannot be used with the include files from this version of Qt."
#error "(The moc has changed too much.)"
#endif

QT_BEGIN_MOC_NAMESPACE
static const uint qt_meta_data_SteamProfilesGUI[] = {

 // content:
       5,       // revision
       0,       // classname
       0,    0, // classinfo
       4,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       0,       // signalCount

 // slots: signature, parameters, type, tag, flags
      18,   17,   17,   17, 0x08,
      37,   17,   17,   17, 0x08,
      64,   17,   17,   17, 0x08,
     101,   95,   90,   17, 0x08,

       0        // eod
};

static const char qt_meta_stringdata_SteamProfilesGUI[] = {
    "SteamProfilesGUI\0\0on_ProfileSubmit()\0"
    "on_actionAbout_triggered()\0"
    "on_actionExit_triggered()\0bool\0event\0"
    "event(QEvent*)\0"
};

const QMetaObject SteamProfilesGUI::staticMetaObject = {
    { &QMainWindow::staticMetaObject, qt_meta_stringdata_SteamProfilesGUI,
      qt_meta_data_SteamProfilesGUI, 0 }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &SteamProfilesGUI::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *SteamProfilesGUI::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *SteamProfilesGUI::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_SteamProfilesGUI))
        return static_cast<void*>(const_cast< SteamProfilesGUI*>(this));
    return QMainWindow::qt_metacast(_clname);
}

int SteamProfilesGUI::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QMainWindow::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        switch (_id) {
        case 0: on_ProfileSubmit(); break;
        case 1: on_actionAbout_triggered(); break;
        case 2: on_actionExit_triggered(); break;
        case 3: { bool _r = event((*reinterpret_cast< QEvent*(*)>(_a[1])));
            if (_a[0]) *reinterpret_cast< bool*>(_a[0]) = _r; }  break;
        default: ;
        }
        _id -= 4;
    }
    return _id;
}
QT_END_MOC_NAMESPACE
