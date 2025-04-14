using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Globalization;
using System.IO;
using System.Linq;
using System.Text.RegularExpressions;
using System.Windows.Forms;

namespace sidorov_students
{
    public partial class MainForm : Form
    {
        private readonly StudentService _studentService;
        private bool _isDataChanged = false;
        private Student _selectedStudent = null;

        public MainForm()
        {
            InitializeComponent();
            InitializeDataGridView();
            _studentService = new StudentService();
            RefreshStudentsList();
            InitializeSortComboBox();
        }

        private void InitializeSortComboBox()
        {
            cmbSort.Items.AddRange(new object[] { "Фамилия", "Группа", "Курс" });
            cmbSort.SelectedIndex = 0;
        }

        private void RefreshStudentsList(List<Student> students = null)
        {
            dgvStudents.Rows.Clear();

            var studentsToShow = students ?? _studentService.Students;

            foreach (var student in studentsToShow)
            {
                dgvStudents.Rows.Add(
                    student.LastName,
                    student.FirstName,
                    student.MiddleName,
                    student.Course,
                    student.Group,
                    student.BirthDate.ToString("dd.MM.yyyy"),
                    student.Email
                );
            }

            lblStudentCount.Text = $"Всего студентов: {studentsToShow.Count}";
        }

        private void btnAdd_Click(object sender, EventArgs e)
        {
            if (!ValidateInputs())
                return;

            var student = new Student
            {
                LastName = txtLastName.Text.Trim(),
                FirstName = txtFirstName.Text.Trim(),
                MiddleName = txtMiddleName.Text.Trim(),
                Course = (int)nudCourse.Value,
                Group = txtGroup.Text.Trim(),
                BirthDate = dtpBirthDate.Value,
                Email = txtEmail.Text.Trim()
            };

            _studentService.AddStudent(student);
            _isDataChanged = true;
            RefreshStudentsList();
            ClearInputs();
        }

        private void btnEdit_Click(object sender, EventArgs e)
        {
            if (_selectedStudent == null || dgvStudents.SelectedRows.Count == 0)
            {
                MessageBox.Show("Выберите студента для редактирования", "Ошибка",
                              MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            if (!ValidateInputs())
                return;

            var updatedStudent = new Student
            {
                LastName = txtLastName.Text.Trim(),
                FirstName = txtFirstName.Text.Trim(),
                MiddleName = txtMiddleName.Text.Trim(),
                Course = (int)nudCourse.Value,
                Group = txtGroup.Text.Trim(),
                BirthDate = dtpBirthDate.Value,
                Email = txtEmail.Text.Trim()
            };

            // Находим индекс выбранного студента
            int selectedIndex = _studentService.Students.IndexOf(_selectedStudent);

            if (selectedIndex >= 0)
            {
                _studentService.Students[selectedIndex] = updatedStudent;
                _isDataChanged = true;
                RefreshStudentsList();

                // Выбираем отредактированную строку
                if (dgvStudents.Rows.Count > selectedIndex)
                {
                    dgvStudents.Rows[selectedIndex].Selected = true;
                }
            }

            ClearInputs();
            _selectedStudent = null;
            btnEdit.Enabled = false;
            btnDelete.Enabled = false;
            btnAdd.Enabled = true;
        }

        private void btnDelete_Click(object sender, EventArgs e)
        {
            if (_selectedStudent == null || dgvStudents.SelectedRows.Count == 0)
            {
                MessageBox.Show("Выберите студента для удаления", "Ошибка",
                              MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            var result = MessageBox.Show(
                $"Удалить студента {_selectedStudent.LastName} {_selectedStudent.FirstName}?",
                "Подтверждение удаления",
                MessageBoxButtons.YesNo,
                MessageBoxIcon.Question);

            if (result == DialogResult.Yes)
            {
                _studentService.RemoveStudent(_selectedStudent);
                _isDataChanged = true;
                RefreshStudentsList();
                ClearInputs();
                _selectedStudent = null;
            }
        }

        private bool ValidateInputs()
        {
            if (string.IsNullOrWhiteSpace(txtLastName.Text))
            {
                MessageBox.Show("Введите фамилию", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                txtLastName.Focus();
                return false;
            }

            if (string.IsNullOrWhiteSpace(txtFirstName.Text))
            {
                MessageBox.Show("Введите имя", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                txtFirstName.Focus();
                return false;
            }

            if (string.IsNullOrWhiteSpace(txtGroup.Text))
            {
                MessageBox.Show("Введите группу", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                txtGroup.Focus();
                return false;
            }

            if (!ValidateEmail(txtEmail.Text))
            {
                MessageBox.Show("Введите корректный email (домены: yandex.ru, gmail.com, icloud.com)", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                txtEmail.Focus();
                return false;
            }

            if (dtpBirthDate.Value > DateTime.Today)
            {
                MessageBox.Show("Дата рождения не может быть в будущем", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                dtpBirthDate.Focus();
                return false;
            }

            if (dtpBirthDate.Value < new DateTime(1992, 1, 1))
            {
                MessageBox.Show("Дата рождения не может быть раньше 01.01.1992", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                dtpBirthDate.Focus();
                return false;
            }

            return true;
        }

        private bool ValidateEmail(string email)
        {
            if (string.IsNullOrWhiteSpace(email))
                return false;

            var pattern = @"^[a-zA-Z0-9._%+-]{3,}@(yandex\.ru|gmail\.com|icloud\.com)$";
            return Regex.IsMatch(email, pattern, RegexOptions.IgnoreCase);
        }

        private void ClearInputs()
        {
            txtLastName.Clear();
            txtFirstName.Clear();
            txtMiddleName.Clear();
            nudCourse.Value = 1;
            txtGroup.Clear();
            dtpBirthDate.Value = DateTime.Today;
            txtEmail.Clear();

            if (dgvStudents.SelectedRows.Count > 0)
            {
                dgvStudents.ClearSelection();
            }
        }

        private void dgvStudents_SelectionChanged(object sender, EventArgs e)
        {
            if (dgvStudents.SelectedRows.Count == 0)
            {
                ClearInputs();
                btnEdit.Enabled = false;
                btnDelete.Enabled = false;
                btnAdd.Enabled = true;
                return;
            }

            var selectedRow = dgvStudents.SelectedRows[0];
            int studentIndex = selectedRow.Index;

            if (studentIndex >= 0 && studentIndex < _studentService.Students.Count)
            {
                _selectedStudent = _studentService.Students[studentIndex];

                txtLastName.Text = _selectedStudent.LastName;
                txtFirstName.Text = _selectedStudent.FirstName;
                txtMiddleName.Text = _selectedStudent.MiddleName;
                nudCourse.Value = _selectedStudent.Course;
                txtGroup.Text = _selectedStudent.Group;
                dtpBirthDate.Value = _selectedStudent.BirthDate;
                txtEmail.Text = _selectedStudent.Email;

                btnAdd.Enabled = false;
                btnEdit.Enabled = true;
                btnDelete.Enabled = true;
            }
        }

        private void btnSave_Click(object sender, EventArgs e)
        {
            _studentService.SaveData();
            _isDataChanged = false;
            MessageBox.Show("Данные сохранены", "Сохранение", MessageBoxButtons.OK, MessageBoxIcon.Information);
        }

        private void MainForm_FormClosing(object sender, FormClosingEventArgs e)
        {
            if (_isDataChanged)
            {
                var result = MessageBox.Show(
                    "Есть несохраненные изменения. Сохранить перед выходом?",
                    "Предупреждение",
                    MessageBoxButtons.YesNoCancel,
                    MessageBoxIcon.Question);

                if (result == DialogResult.Yes)
                {
                    _studentService.SaveData();
                }
                else if (result == DialogResult.Cancel)
                {
                    e.Cancel = true;
                }
            }
        }

        private void btnSort_Click(object sender, EventArgs e)
        {
            switch (cmbSort.SelectedItem.ToString())
            {
                case "Фамилия":
                    _studentService.SortByLastName();
                    break;
                case "Группа":
                    _studentService.SortByGroup();
                    break;
                case "Курс":
                    _studentService.SortByCourse();
                    break;
            }

            RefreshStudentsList();
        }

        private void btnFilter_Click(object sender, EventArgs e)
        {
            int? course = null;
            if (!string.IsNullOrWhiteSpace(txtFilterCourse.Text) && int.TryParse(txtFilterCourse.Text, out int courseValue))
            {
                course = courseValue;
            }

            var filteredStudents = _studentService.FilterByCourseAndGroup(course, txtFilterGroup.Text);
            RefreshStudentsList(filteredStudents);
        }

        private void btnSearch_Click(object sender, EventArgs e)
        {
            if (string.IsNullOrWhiteSpace(txtSearchLastName.Text))
            {
                RefreshStudentsList();
                return;
            }

            var foundStudents = _studentService.SearchByLastName(txtSearchLastName.Text);
            RefreshStudentsList(foundStudents);
        }

        private void btnExport_Click(object sender, EventArgs e)
        {
            using (var saveFileDialog = new SaveFileDialog())
            {
                saveFileDialog.Filter = "CSV файлы (*.csv)|*.csv";
                saveFileDialog.Title = "Экспорт данных";
                saveFileDialog.FileName = "students_export.csv";

                if (saveFileDialog.ShowDialog() == DialogResult.OK)
                {
                    _studentService.ExportToCsv(saveFileDialog.FileName);
                    MessageBox.Show("Данные успешно экспортированы", "Экспорт", MessageBoxButtons.OK, MessageBoxIcon.Information);
                }
            }
        }

        private void btnImport_Click(object sender, EventArgs e)
        {
            using (var openFileDialog = new OpenFileDialog())
            {
                openFileDialog.Filter = "CSV файлы (*.csv)|*.csv";
                openFileDialog.Title = "Импорт данных";

                if (openFileDialog.ShowDialog() == DialogResult.OK)
                {
                    try
                    {
                        _studentService.ImportFromCsv(openFileDialog.FileName);
                        _isDataChanged = true;
                        RefreshStudentsList();
                        MessageBox.Show("Данные успешно импортированы", "Импорт", MessageBoxButtons.OK, MessageBoxIcon.Information);
                    }
                    catch (Exception ex)
                    {
                        MessageBox.Show($"Ошибка при импорте данных: {ex.Message}", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Error);
                    }
                }
            }
        }

        private void btnClearFilters_Click(object sender, EventArgs e)
        {
            txtFilterCourse.Clear();
            txtFilterGroup.Clear();
            txtSearchLastName.Clear();
            RefreshStudentsList();
        }

        private void InitializeDataGridView()
        {
            // Очищаем существующие столбцы
            dgvStudents.Columns.Clear();

            // Добавляем столбцы
            dgvStudents.Columns.Add("LastName", "Фамилия");
            dgvStudents.Columns.Add("FirstName", "Имя");
            dgvStudents.Columns.Add("MiddleName", "Отчество");
            dgvStudents.Columns.Add("Course", "Курс");
            dgvStudents.Columns.Add("Group", "Группа");
            dgvStudents.Columns.Add("BirthDate", "Дата рождения");
            dgvStudents.Columns.Add("Email", "Email");

            // Настройка свойств DataGridView
            dgvStudents.AutoSizeColumnsMode = DataGridViewAutoSizeColumnsMode.Fill;
            dgvStudents.AllowUserToAddRows = false;
            dgvStudents.ReadOnly = true;
            dgvStudents.SelectionMode = DataGridViewSelectionMode.FullRowSelect;
        }
    }
}