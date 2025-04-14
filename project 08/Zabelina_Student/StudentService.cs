using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;

namespace sidorov_students
{
    public class StudentService
    {
        private List<Student> _students = new List<Student>();
        private readonly string _dataFilePath = "students.json";

        public List<Student> Students => _students;

        public StudentService()
        {
            LoadData();
        }

        public void AddStudent(Student student)
        {
            _students.Add(student);
        }

        public void RemoveStudent(Student student)
        {
            _students.Remove(student);
        }

        public void UpdateStudent(Student oldStudent, Student newStudent)
        {
            var index = _students.IndexOf(oldStudent);
            if (index != -1)
            {
                _students[index] = newStudent;
            }
        }

        public List<Student> SearchByLastName(string lastName)
        {
            return _students.Where(s =>
                s.LastName.IndexOf(lastName, StringComparison.OrdinalIgnoreCase) >= 0
            ).ToList();
        }

        public List<Student> FilterByCourseAndGroup(int? course, string group)
        {
            return _students.Where(s =>
                (course == null || s.Course == course) &&
                (string.IsNullOrEmpty(group) || s.Group.Equals(group, StringComparison.OrdinalIgnoreCase))
            ).ToList();
        }

        public void SortByLastName()
        {
            _students = _students.OrderBy(s => s.LastName).ToList();
        }

        public void SortByGroup()
        {
            _students = _students.OrderBy(s => s.Group).ToList();
        }

        public void SortByCourse()
        {
            _students = _students.OrderBy(s => s.Course).ToList();
        }

        public void SaveData()
        {
            string json = JsonConvert.SerializeObject(_students, Formatting.Indented);
            File.WriteAllText(_dataFilePath, json);
        }

        private void LoadData()
        {
            if (File.Exists(_dataFilePath))
            {
                string json = File.ReadAllText(_dataFilePath);
                _students = JsonConvert.DeserializeObject<List<Student>>(json) ?? new List<Student>();
            }
        }

        public void ExportToCsv(string filePath)
        {
            using (var writer = new StreamWriter(filePath))
            {
                writer.WriteLine("LastName;FirstName;MiddleName;Course;Group;BirthDate;Email");
                foreach (var student in _students)
                {
                    writer.WriteLine($"{student.LastName};{student.FirstName};{student.MiddleName};{student.Course};{student.Group};{student.BirthDate:dd.MM.yyyy};{student.Email}");
                }
            }
        }

        public void ImportFromCsv(string filePath)
        {
            var newStudents = new List<Student>();
            using (var reader = new StreamReader(filePath))
            {
                // Пропускаем заголовок
                reader.ReadLine();

                while (!reader.EndOfStream)
                {
                    var line = reader.ReadLine();
                    var values = line.Split(';');

                    if (values.Length == 7)
                    {
                        newStudents.Add(new Student
                        {
                            LastName = values[0],
                            FirstName = values[1],
                            MiddleName = values[2],
                            Course = int.Parse(values[3]),
                            Group = values[4],
                            BirthDate = DateTime.ParseExact(values[5], "dd.MM.yyyy", null),
                            Email = values[6]
                        });
                    }
                }
            }

            _students = newStudents;
        }
    }
}