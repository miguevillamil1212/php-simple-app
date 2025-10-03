pipeline {
  agent any

  environment {
    IMAGE_NAME = "miguel1212/php-simple-app"
  }

  stages {
    stage('Checkout') {
      steps {
        git branch: 'main', url: 'https://github.com/miguevillamil1212/php-simple-app.git'
      }
    }

    stage('Build Docker Image') {
      steps {
        sh '''
          docker build \
            -t $IMAGE_NAME:latest \
            -t $IMAGE_NAME:${BUILD_NUMBER} .
        '''
      }
    }

    stage('Login & Push to DockerHub') {
      steps {
        withCredentials([usernamePassword(
          credentialsId: 'dockerhub-cred',
          usernameVariable: 'DOCKERHUB_USER',
          passwordVariable: 'DOCKERHUB_PASS'
        )]) {
          sh '''
            echo "$DOCKERHUB_PASS" | docker login -u "$DOCKERHUB_USER" --password-stdin
            docker push $IMAGE_NAME:latest
            docker push $IMAGE_NAME:${BUILD_NUMBER}
          '''
        }
      }
      post {
        always {
          // Evita dejar sesión abierta
          sh 'docker logout || true'
        }
      }
    }

    stage('Cleanup') {
      steps {
        // Aquí sí hay node/workspace, no falla FilePath
        sh 'docker system prune -f || true'
      }
    }
  }

  post {
    success {
      echo "Pipeline completado con éxito"
    }
    failure {
      echo "Pipeline falló"
    }
  }
}