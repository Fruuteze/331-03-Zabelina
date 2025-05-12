package mudule_02_gadzhibatyrov.src;

import module_01_gadzhibatyrov.src.DbConnector;
import module_01_gadzhibatyrov.src.ExcelToDB;
import mudule_02_gadzhibatyrov.src.ui.PartnerUI;

import java.sql.Connection;

public class Main {
    public static void main(String[] args) {
        Connection connection = DbConnector.connect();
        if (connection != null) {
            ExcelToDB.importExcel(connection); // Импорт из папки "data/"
            new PartnerUI(new DbConnector()); // Запуск UI
        }
    }
}

