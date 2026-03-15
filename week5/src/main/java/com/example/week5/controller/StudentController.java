package com.example.week5.controller;

import java.util.List;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

import org.springframework.data.domain.Sort;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;

import com.example.week5.entity.Student;
import com.example.week5.repository.StudentRepository;

@RestController
public class StudentController {

    @Autowired
    private StudentRepository studentRepository;

    // CREATE
    @PostMapping("/students")
    public Student addStudent(@RequestBody Student student) {
        return studentRepository.save(student);
    }

    // READ ALL
    @GetMapping("/students")
    public List<Student> getAllStudents() {
        return studentRepository.findAll();
    }

    // SEARCH BY DEPARTMENT
    @GetMapping("/students/department/{department}")
    public List<Student> getByDepartment(@PathVariable String department) {
        return studentRepository.findByDepartment(department);
    }

    // SEARCH BY AGE
    @GetMapping("/students/age/{age}")
    public List<Student> getByAge(@PathVariable int age) {
        return studentRepository.findByAge(age);
    }

    // DELETE
    @DeleteMapping("/students/{id}")
    public String deleteStudent(@PathVariable int id) {
        studentRepository.deleteById(id);
        return "Student deleted successfully";
    }

    // SORTING
    @GetMapping("/students/sort/{field}")
    public List<Student> sortStudents(@PathVariable String field) {
        return studentRepository.findAll(Sort.by(Sort.Direction.ASC, field));
    }

    // PAGINATION
    @GetMapping("/students/page/{page}/{size}")
    public Page<Student> getStudentsPage(@PathVariable int page, @PathVariable int size) {
        return studentRepository.findAll(PageRequest.of(page, size));
    }
}