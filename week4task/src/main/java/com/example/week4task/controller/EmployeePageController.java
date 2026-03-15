package com.example.week4task.controller;

import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ResponseBody;
import org.springframework.stereotype.Controller;

@Controller
public class EmployeePageController {

    @GetMapping("/employeePage")
    @ResponseBody
    public String showEmployeePage() {

        return "<html>"
                + "<head><title>Employee Details</title></head>"
                + "<body>"
                + "<h2>Employee Information</h2>"
                + "<p>Name: Arun</p>"
                + "<p>Role: Software Developer</p>"
                + "</body>"
                + "</html>";
    }
}
