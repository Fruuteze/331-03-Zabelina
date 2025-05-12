package mudule_02_gadzhibatyrov.src.ui;

import module_01_gadzhibatyrov.src.DbConnector;

import javax.swing.*;
import java.awt.*;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;

public class PartnerUI extends JFrame {
    private final DbConnector db;
    private JPanel partnerListPanel;

    public PartnerUI(DbConnector dbConnector) {
        this.db = dbConnector;
        setTitle("Система управления партнёрами");
        setSize(900, 600);
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLayout(new BorderLayout());

        // Панель управления
        JPanel controlPanel = new JPanel();
        JButton addButton = new JButton("➕ Добавить");
        JButton editButton = new JButton("✏ Редактировать");
        JButton deleteButton = new JButton("❌ Удалить");

        addButton.addActionListener(e -> addPartner());
        editButton.addActionListener(e -> editPartner());
        deleteButton.addActionListener(e -> deletePartner());

        controlPanel.add(addButton);
        controlPanel.add(editButton);
        controlPanel.add(deleteButton);
        add(controlPanel, BorderLayout.NORTH);

        // Панель списка партнёров
        partnerListPanel = new JPanel();
        partnerListPanel.setLayout(new GridLayout(0, 1, 10, 10));
        add(new JScrollPane(partnerListPanel), BorderLayout.CENTER);

        loadPartners(); // Загружаем список

        setVisible(true);
    }

    private void loadPartners() {
        Connection connection = db.connect();
        if (connection == null) return;

        try {
            String sql = "SELECT partner_id, partner_type, name, director, phone, rating FROM partners";
            PreparedStatement stmt = connection.prepareStatement(sql);
            ResultSet rs = stmt.executeQuery();

            partnerListPanel.removeAll();

            while (rs.next()) {
                JPanel card = new JPanel(new BorderLayout());
                card.setBorder(BorderFactory.createLineBorder(Color.BLACK, 2));
                card.setBackground(Color.WHITE);
                card.setPreferredSize(new Dimension(850, 100)); // Фиксированная высота

                // Основной текст
                JLabel typeLabel = new JLabel(rs.getString("partner_type") + " | " + rs.getString("name"));
                typeLabel.setFont(new Font("Arial", Font.BOLD, 16));

                JLabel directorLabel = new JLabel("Директор: " + rs.getString("director"));
                JLabel phoneLabel = new JLabel("Телефон: " + rs.getString("phone"));
                JLabel ratingLabel = new JLabel("Рейтинг: " + rs.getInt("rating"));

                JPanel textPanel = new JPanel(new GridLayout(3, 1));
                textPanel.add(typeLabel);
                textPanel.add(directorLabel);
                textPanel.add(phoneLabel);

                // Добавляем текст в карточку
                card.add(textPanel, BorderLayout.WEST);
                card.add(ratingLabel, BorderLayout.EAST);

                partnerListPanel.add(card);
            }

            partnerListPanel.revalidate();
            partnerListPanel.repaint();

            connection.close();

        } catch (Exception e) {
            System.out.println("Ошибка загрузки данных: " + e.getMessage());
        }
    }


    private void addPartner() {
        String[] partnerTypes = {"ООО", "ЗАО", "ИП", "ПАО", "Госкорпорация"};
        JComboBox<String> typeBox = new JComboBox<>(partnerTypes);
        JOptionPane.showMessageDialog(this, typeBox, "Выберите тип партнёра", JOptionPane.QUESTION_MESSAGE);
        String partnerType = (String) typeBox.getSelectedItem();

        String name = JOptionPane.showInputDialog(this, "Введите название партнёра:");
        if (name == null || name.trim().isEmpty()) return;

        String director = JOptionPane.showInputDialog(this, "Введите имя директора:");
        if (director == null || director.trim().isEmpty()) return;

        String phone = JOptionPane.showInputDialog(this, "Введите номер телефона:");
        if (phone == null || phone.trim().isEmpty()) return;

        String ratingStr = JOptionPane.showInputDialog(this, "Введите рейтинг (1-10):");
        if (ratingStr == null || ratingStr.trim().isEmpty()) return;

        int rating = Integer.parseInt(ratingStr);

        Connection connection = db.connect();
        if (connection != null) {
            try {
                String sql = "INSERT INTO partners (partner_type, name, director, phone, rating) VALUES (?, ?, ?, ?, ?)";
                PreparedStatement stmt = connection.prepareStatement(sql);
                stmt.setString(1, partnerType);
                stmt.setString(2, name);
                stmt.setString(3, director);
                stmt.setString(4, phone);
                stmt.setInt(5, rating);
                stmt.executeUpdate();
                loadPartners();
                connection.close();
            } catch (Exception e) {
                System.out.println("Ошибка добавления партнёра: " + e.getMessage());
            }
        }
    }

    private String selectPartner(String message) {
        Connection connection = db.connect();
        if (connection == null) return null;

        try {
            String sql = "SELECT name FROM partners";
            PreparedStatement stmt = connection.prepareStatement(sql);
            ResultSet rs = stmt.executeQuery();

            DefaultListModel<String> model = new DefaultListModel<>();
            while (rs.next()) {
                model.addElement(rs.getString("name"));
            }
            connection.close();

            JList<String> list = new JList<>(model);
            int option = JOptionPane.showConfirmDialog(this, new JScrollPane(list), message, JOptionPane.OK_CANCEL_OPTION);

            return (option == JOptionPane.OK_OPTION) ? list.getSelectedValue() : null;

        } catch (Exception e) {
            System.out.println("Ошибка загрузки списка партнёров: " + e.getMessage());
            return null;
        }
    }


    private void editPartner() {
        String selectedPartner = selectPartner("Выберите партнёра для редактирования:");
        if (selectedPartner == null) return;

        String[] partnerTypes = {"ООО", "ЗАО", "ИП", "ПАО", "Госкорпорация"};
        JComboBox<String> typeBox = new JComboBox<>(partnerTypes);
        JOptionPane.showMessageDialog(this, typeBox, "Выберите новый тип партнёра", JOptionPane.QUESTION_MESSAGE);
        String partnerType = (String) typeBox.getSelectedItem();

        String name = JOptionPane.showInputDialog(this, "Введите новое название:");
        String director = JOptionPane.showInputDialog(this, "Введите имя директора:");
        String phone = JOptionPane.showInputDialog(this, "Введите новый телефон:");
        String ratingStr = JOptionPane.showInputDialog(this, "Введите новый рейтинг (1-10):");
        if (ratingStr == null || ratingStr.trim().isEmpty()) return;

        int rating = Integer.parseInt(ratingStr);

        Connection connection = db.connect();
        if (connection != null) {
            try {
                String sql = "UPDATE partners SET partner_type = ?, name = ?, director = ?, phone = ?, rating = ? WHERE name = ?";
                PreparedStatement stmt = connection.prepareStatement(sql);
                stmt.setString(1, partnerType);
                stmt.setString(2, name);
                stmt.setString(3, director);
                stmt.setString(4, phone);
                stmt.setInt(5, rating);
                stmt.setString(6, selectedPartner);
                stmt.executeUpdate();
                loadPartners();
                connection.close();
            } catch (Exception e) {
                System.out.println("Ошибка редактирования партнёра: " + e.getMessage());
            }
        }
    }

    private void deletePartner() {
        String selectedPartner = selectPartner("Выберите партнёра для удаления:");
        if (selectedPartner == null) return;

        Connection connection = db.connect();
        if (connection != null) {
            try {
                String sql = "DELETE FROM partners WHERE name = ?";
                PreparedStatement stmt = connection.prepareStatement(sql);
                stmt.setString(1, selectedPartner);
                int rowsAffected = stmt.executeUpdate();
                connection.close();

                if (rowsAffected == 0) {
                    JOptionPane.showMessageDialog(this, "❌ Партнёр не найден!", "Ошибка", JOptionPane.ERROR_MESSAGE);
                } else {
                    JOptionPane.showMessageDialog(this, "✅ Партнёр удалён!", "Успех", JOptionPane.INFORMATION_MESSAGE);
                    loadPartners();
                }
            } catch (Exception e) {
                System.out.println("Ошибка удаления партнёра: " + e.getMessage());
            }
        }
    }
}
