package module_01_gadzhibatyrov.src;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.Date;
import java.sql.ResultSet;
import java.time.LocalDate;
import java.util.Random;
import java.io.File;
import java.io.FileInputStream;
import org.apache.poi.ss.usermodel.*;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;

public class ExcelToDB {
    public static void importExcel(Connection connection) {
        String filePath = "D:\\intelejii\\project 09\\src\\main\\java\\module_01_gadzhibatyrov\\import_data\\partners_import.xlsx";

        try (FileInputStream fis = new FileInputStream(new File(filePath));
             Workbook workbook = new XSSFWorkbook(fis)) {

            Sheet sheet = workbook.getSheetAt(0);
            String sql = "INSERT INTO partners (partner_type, name, director, email, phone, legal_address, inn, rating, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            PreparedStatement preparedStatement = connection.prepareStatement(sql);

            Random random = new Random();
            LocalDate today = LocalDate.now();

            for (Row row : sheet) {
                if (row.getRowNum() == 0) continue;  // Пропускаем заголовок

                String partnerType = getCellValue(row.getCell(0));
                String name = getCellValue(row.getCell(1));
                String director = getCellValue(row.getCell(2));
                String email = getCellValue(row.getCell(3));
                String phone = getCellValue(row.getCell(4));
                String legalAddress = getCellValue(row.getCell(5));
                String inn = getCellValue(row.getCell(6));
                String ratingStr = getCellValue(row.getCell(7));

                // Проверяем пустые значения
                if (partnerType.isEmpty()) partnerType = "Не указан";
                if (ratingStr.isEmpty()) ratingStr = "0"; // Устанавливаем рейтинг по умолчанию

                int rating = Integer.parseInt(ratingStr);

                // **Проверка на дубликаты**: если партнёр уже существует, пропускаем его
                if (isDuplicate(connection, name)) {
                    continue;
                }

                // Определяем дату регистрации: случайная для Excel, текущая при добавлении
                LocalDate registrationDate;
                if (row.getCell(8) == null || row.getCell(8).getCellType() == CellType.BLANK) {
                    int year = random.nextInt(today.getYear() - 2015 + 1) + 2015;
                    int month = random.nextInt(12) + 1;
                    int day = random.nextInt(28) + 1;
                    registrationDate = LocalDate.of(year, month, day);
                } else {
                    registrationDate = today;
                }

                // Вставляем данные в БД
                preparedStatement.setString(1, partnerType);
                preparedStatement.setString(2, name);
                preparedStatement.setString(3, director);
                preparedStatement.setString(4, email);
                preparedStatement.setString(5, phone);
                preparedStatement.setString(6, legalAddress);
                preparedStatement.setString(7, inn);
                preparedStatement.setInt(8, rating);
                preparedStatement.setDate(9, Date.valueOf(registrationDate));
                preparedStatement.executeUpdate();
            }

            System.out.println("✅ Данные загружены в БД без дубликатов и с корректными датами!");

        } catch (Exception e) {
            System.out.println("❌ Ошибка загрузки Excel: " + e.getMessage());
        }
    }

    private static boolean isDuplicate(Connection connection, String name) {
        try {
            String checkSql = "SELECT COUNT(*) FROM partners WHERE name = ?";
            PreparedStatement checkStmt = connection.prepareStatement(checkSql);
            checkStmt.setString(1, name);
            ResultSet rs = checkStmt.executeQuery();
            if (rs.next() && rs.getInt(1) > 0) {
                return true; // Дубликат найден
            }
        } catch (Exception e) {
            System.out.println("❌ Ошибка проверки дубликатов: " + e.getMessage());
        }
        return false; // Дубликатов нет
    }

    private static String getCellValue(Cell cell) {
        if (cell == null) return "";
        return switch (cell.getCellType()) {
            case STRING -> cell.getStringCellValue();
            case NUMERIC -> String.valueOf((int) cell.getNumericCellValue());
            default -> "";
        };
    }
}
