This is a Moodle Block Plugin which offers the user an overview of all the Assignments of the Courses to which the user has enrolled.

The plugin is represented by a tree with each node in the tree being an enrolled course along with an icon representing the status of the assignments of that course. 
The icon possibilities are as follows:
1) Red exclamation mark - At least one assignment is not submitted.
2) Yellow tick - All the assignments of the course have been submitted but not graded.
3) Green tick - All the assignments of the course have been submitted and graded.

When a course is expanded, a short count of the assignment statuses is displayed.
eg) [T:10, G:3, S:5, N:2]
	T: Total assignments
	G: No of graded assignments
	S: No of submitted but not graded assignments
	N: No of not submitted assignments

Each assignment has a further indicator to the left of the assignment name to indicate the assignment status.
1) Red - Not submitted
2) Yellow - Submitted but not graded
3) Green - Submitted and graded

Each assignment ca be further expanded to show details of the assignment.
1) If assignment is red, then status, due date and time remaining is displayed.
2) If assignment is yellow, then status, date of submission and due date is displayed.
3) If assignment is green, then status and grade is displayed.

All the assignment and course names are clickable and redirect the user to the respective page.
