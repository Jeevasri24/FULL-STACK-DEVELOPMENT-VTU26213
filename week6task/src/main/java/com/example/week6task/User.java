package com.example.week6task;

import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;
import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.Min;

public class User {

    @NotNull(message="Name cannot be null")
    @Size(min=3, message="Name must have at least 3 characters")
    private String name;

    @Email(message="Invalid email format")
    private String email;

    @Min(value=18, message="Age must be greater than or equal to 18")
    private int age;

    public User() {
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public int getAge() {
        return age;
    }

    public void setAge(int age) {
        this.age = age;
    }
}