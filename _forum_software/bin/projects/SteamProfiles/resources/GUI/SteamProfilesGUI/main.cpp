#include <QtGui/QApplication>
#include "steamprofilesgui.h"

int main(int argc, char *argv[])
{
    QApplication Application(argc, argv);
    SteamProfilesGUI Widget;
    Widget.show();

    return Application.exec();
}
