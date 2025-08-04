# Import necessary libraries
import numpy as np
from datetime import datetime

# Initialize employee names
employees = ["Alice", "Bob", "Charlie", "David", "Eve"]

# Initialize rating as a global variable with random integer values between 1 and 10
rating = np.random.randint(1, 11, (12, len(employees)))

# Function to get employee data
def get_employee_data():
    global rating, employees
    # Get the current month
    current_month = datetime.now().month - 1
    # Initialize list for new employees
    new_employees = []

    # Loop to input new employee data
    while True:
        name = input("Enter employee name (or 'done' to finish): ")
        # Break loop if 'done' is entered
        if name.lower() == 'done':
            break
        # Add new employee name to the list
        new_employees.append(name)
        # Initialize ratings up to current month
        new_rating = [0] * current_month

        # Loop to input ratings for remaining months
        for i in range(current_month, 12):
            new_rating_element = int(input(f"Enter rating for {name} for month {i + 1}: "))
            new_rating.append(new_rating_element)

        # Add the new employee ratings to the global rating array
        rating = np.column_stack((rating, new_rating))
        print(f"Employee {name} added.")

    # Update the global employees list with new employees
    employees.extend(new_employees)

# Function to calculate total performance of each employee
def calculate_total_performance():
    global rating, employees
    # Calculate total performance for each employee
    total_performance = np.sum(rating, axis=0)
    # Print total performance for each employee
    for i, employee in enumerate(employees):
        print(f"The total performance of {employee} is {total_performance[i]}")

# Function to calculate average performance of each employee
def calculate_average_performance():
    global rating, employees
    # Calculate average performance for each employee
    average_performance = np.mean(rating, axis=0)
    # Print average performance for each employee
    for i, employee in enumerate(employees):
        print(f"The average performance for {employee} is {average_performance[i]:.2f}")

# Function to identify employees with total performance rating greater than 80
def identify_employee_rating_greaterthan_80():
    global rating, employees
    # Calculate total performance for each employee
    total_performance = np.sum(rating, axis=0)
    # Print employees with total performance greater than 80
    for i, employee in enumerate(employees):
        if total_performance[i] > 80:
            print(f"Employee {employee} has a total performance greater than 80 with performance score of {total_performance[i]}.")

# Function to identify underperformers with total performance less than 50
def identify_under_performer():
    global rating, employees
    # Calculate total performance for each employee
    total_performance = np.sum(rating, axis=0)
    # Print employees with total performance less than 50
    for i, employee in enumerate(employees):
        if total_performance[i] < 50:
            print(f"{employee} has not met the performance standards with performance score of {total_performance[i]}")

# Function to assign performance grades to employees
def assign_performance_grades():
    global rating, employees
    # Calculate total score for each employee
    total_score = np.sum(rating, axis=0)
    # Assign grades based on total score
    for i, employee in enumerate(employees):
        if total_score[i] > 95:
            print(f"{employee} has been assigned 'A' Grade with Score of {total_score[i]}")
        elif total_score[i] > 80:
            print(f"{employee} has been assigned 'B' Grade with Score of {total_score[i]}")
        elif total_score[i] > 60:
            print(f"{employee} has been assigned 'C' Grade with Score of {total_score[i]}")
        else:
            print(f"{employee} has not met the standards with Score of {total_score[i]}")

# Function to rank all employees based on total score
def ranking_all_employee():
    global rating, employees
    # Calculate total score for each employee
    total_score = np.sum(rating, axis=0)
    # Sort employees based on total score in descending order
    sort_index = np.argsort(total_score)[::-1]
    sorted_employees = [employees[i] for i in sort_index]
    # Print rankings of employees
    for i, employee in enumerate(sorted_employees):
        print(f"Rank {i + 1}: {employee} with Score of {total_score[sort_index[i]]}")

# Save the results to a text file
def save_to_file():
    global employees, rating
    with open("results.txt", "w") as f:
        f.write("Employees:\n")
        for employee in employees:
            f.write(employee + "\n")
        f.write("Ratings:\n")
        for i in range(len(employees)):
            f.write(str(rating[:, i]) + "\n")

# Load the results from a text file
def load_from_file():
    global employees, rating
    with open("results.txt", "r") as f:
        lines = f.readlines()
        employees = lines[1: len(employees) + 1]
        ratings = lines[len(employees) + 2:]
        rating = np.array([list(map(int, r.strip("[]\n").split())) for r in ratings])

# Main loop to run the Employee Performance Management System
while True:
    # Print menu options
    print("\nEmployee Performance Management System")
    print("1. Add Employee Data")
    print("2. Calculate Total Performance")
    print("3. Calculate Average Performance")
    print("4. Identify Employees with Rating Greater than 80")
    print("5. Identify Underperformers")
    print("6. Assign Performance Grades")
    print("7. Rank All Employees")
    print("8. Save Results to File")
    print("9. Load Results from File")
    print("10. Exit")

    # Get user choice
    choice = input("Enter your choice: ")

    # Execute function based on user choice
    if choice == '1':
        get_employee_data()
    elif choice == '2':
        calculate_total_performance()
    elif choice == '3':
        calculate_average_performance()
    elif choice == '4':
        identify_employee_rating_greaterthan_80()
    elif choice == '5':
        identify_under_performer()
    elif choice == '6':
        assign_performance_grades()
    elif choice == '7':
        ranking_all_employee()
    elif choice == '8':
        save_to_file()
    elif choice == '9':
        load_from_file()
    elif choice == '10':
        break
    else:
        print("Invalid choice, please try again.")

