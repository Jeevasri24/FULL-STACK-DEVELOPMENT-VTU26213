package com.example.week4task.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;

import com.example.week4task.service.EmployeeService;

@RestController
public class EmployeeController {

    @Autowired
    EmployeeService service;

    @GetMapping("/employee")
    public String getEmployee() {
        return service.getEmployeeDetails();
    }

}