pipeline {
    agent any

    environment {
        DOCKERHUB_CREDENTIALS = credentials('dockerhub-cred')  // crea credencial en Jenkins con tu usuario/pass de DockerHub
        IMAGE_NAME = "jamescanos/php-simple-app"
    }

    stages {
        stage('Checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/jamescanos/php-simple-app.git'
            }
        }

        stage('Build Docker Image') {
            steps {
                sh 'docker build -t $IMAGE_NAME:latest .'
            }
        }

        stage('Login to DockerHub') {
            steps {
                sh 'echo $DOCKERHUB_CREDENTIALS_PSW | docker login -u $DOCKERHUB_CREDENTIALS_USR --password-stdin'
            }
        }

        stage('Push to DockerHub') {
            steps {
                sh 'docker push $IMAGE_NAME:latest'
            }
        }
    }

    post {
        always {
            echo "=== Limpieza final ==="
            sh 'docker system prune -f || true'
        }
        success {
            echo "Pipeline completado con éxito"
        }
        failure {
            echo "Pipeline falló"
        }
    }
}
